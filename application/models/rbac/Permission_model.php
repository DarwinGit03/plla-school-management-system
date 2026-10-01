<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model
{
    public function get_catalog()
    {
        return $this->db->order_by('module', 'ASC')->order_by('name', 'ASC')->get('permissions')->result();
    }

    public function get_effective_permissions($user_id)
    {
        $rows = $this->db
            ->select('permissions.permission_key, user_permission_overrides.effect AS override_effect, role_permissions.permission_id AS role_permission_id, users.is_active AS user_active, roles.is_active AS role_active')
            ->from('users')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->join('permissions', '1 = 1', 'inner', false)
            ->join('role_permissions', 'role_permissions.role_id = roles.id AND role_permissions.permission_id = permissions.id', 'left', false)
            ->join('user_permission_overrides', 'user_permission_overrides.user_id = users.id AND user_permission_overrides.permission_id = permissions.id', 'left', false)
            ->where('users.id', (int) $user_id)
            ->get()
            ->result();

        $effective = [];
        foreach ($rows as $row) {
            if (!(int) $row->user_active || !(int) $row->role_active) continue;
            $allowed = $row->override_effect === 'allow'
                || ($row->override_effect === null && $row->role_permission_id !== null);
            if ($allowed) $effective[$row->permission_key] = true;
        }
        return $effective;
    }

    public function get_user_access($user_id)
    {
        $user = $this->db
            ->select('users.id, users.username, users.email, users.first_name, users.last_name, users.role_id, users.is_active, roles.role_name, roles.is_active AS role_active')
            ->from('users')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.id', (int) $user_id)
            ->get()->row();
        if (!$user) return null;

        $overrides = $this->db->where('user_id', (int) $user_id)->get('user_permission_overrides')->result();
        $overrideMap = [];
        foreach ($overrides as $override) $overrideMap[(int) $override->permission_id] = $override;

        $roleGrants = $this->db->select('permission_id')->where('role_id', (int) $user->role_id)->get('role_permissions')->result();
        $roleMap = [];
        foreach ($roleGrants as $grant) $roleMap[(int) $grant->permission_id] = true;

        $permissions = $this->get_catalog();
        foreach ($permissions as $permission) {
            $id = (int) $permission->id;
            $permission->role_granted = isset($roleMap[$id]);
            $permission->override_effect = isset($overrideMap[$id]) ? $overrideMap[$id]->effect : 'inherit';
            $permission->override_reason = isset($overrideMap[$id]) ? $overrideMap[$id]->reason : '';
            $permission->effective = $permission->override_effect === 'allow'
                || ($permission->override_effect === 'inherit' && $permission->role_granted);
        }
        return ['user' => $user, 'permissions' => $permissions];
    }

    public function save_user_access($user_id, $role_id, array $overrides, $actor, $reason = '')
    {
        $user = $this->db->where('id', (int) $user_id)->get('users')->row();
        $role = $this->db->where(['id' => (int) $role_id, 'is_active' => 1])->get('roles')->row();
        if (!$user || !$role) return ['ok' => false, 'code' => 'invalid_user_or_role'];

        $permissionIds = [];
        foreach ($this->get_catalog() as $permission) $permissionIds[(int) $permission->id] = true;
        foreach ($overrides as $permission_id => $effect) {
            if (!isset($permissionIds[(int) $permission_id]) || !in_array($effect, ['inherit', 'allow', 'deny'], true)) {
                return ['ok' => false, 'code' => 'invalid_permission'];
            }
        }

        $this->db->trans_begin();
        $this->db->where('id', (int) $user_id)->update('users', ['role_id' => (int) $role_id]);
        $this->db->where('user_id', (int) $user_id)->delete('user_permission_overrides');
        $rows = [];
        foreach ($overrides as $permission_id => $effect) {
            if ($effect === 'inherit') continue;
            $rows[] = [
                'user_id' => (int) $user_id,
                'permission_id' => (int) $permission_id,
                'effect' => $effect,
                'reason' => $reason !== '' ? substr($reason, 0, 255) : null,
                'updated_by' => (string) $actor,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        if ($rows) $this->db->insert_batch('user_permission_overrides', $rows);
        if ($this->count_active_user_managers() < 1) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'last_admin'];
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true];
    }

    public function get_role_access($role_id)
    {
        $role = $this->db->where('id', (int) $role_id)->get('roles')->row();
        if (!$role) return null;
        $grants = $this->db->select('permission_id')->where('role_id', (int) $role_id)->get('role_permissions')->result();
        $grantMap = [];
        foreach ($grants as $grant) $grantMap[(int) $grant->permission_id] = true;
        $permissions = $this->get_catalog();
        foreach ($permissions as $permission) $permission->granted = isset($grantMap[(int) $permission->id]);
        return ['role' => $role, 'permissions' => $permissions];
    }

    public function save_role_access($role_id, $name, $description, $active, array $permission_ids)
    {
        $name = trim((string) $name);
        if ($name === '' || strlen($name) > 100) return ['ok' => false, 'code' => 'invalid_role'];
        $catalog = $this->get_catalog();
        $validIds = [];
        foreach ($catalog as $permission) $validIds[(int) $permission->id] = true;
        foreach ($permission_ids as $id) if (!isset($validIds[(int) $id])) return ['ok' => false, 'code' => 'invalid_permission'];
        $existing = $role_id > 0 ? $this->db->where('id', (int) $role_id)->get('roles')->row() : null;
        if ($role_id > 0 && !$existing) return ['ok' => false, 'code' => 'not_found'];
        if ($existing && (int) $existing->is_system === 1) {
            $allIds = array_map(function ($permission) { return (int) $permission->id; }, $catalog);
            if (!$active || array_diff($allIds, array_map('intval', $permission_ids))) {
                return ['ok' => false, 'code' => 'system_role_protected'];
            }
        }

        $this->db->trans_begin();
        $roleData = ['role_name' => $name, 'description' => trim((string) $description), 'is_active' => $active ? 1 : 0];
        if ($existing) $this->db->where('id', (int) $role_id)->update('roles', $roleData);
        else $this->db->insert('roles', $roleData);
        $savedRoleId = $existing ? (int) $role_id : (int) $this->db->insert_id();
        $this->db->where('role_id', $savedRoleId)->delete('role_permissions');
        $rows = [];
        foreach (array_unique(array_map('intval', $permission_ids)) as $permission_id) {
            $rows[] = ['role_id' => $savedRoleId, 'permission_id' => $permission_id];
        }
        if ($rows) $this->db->insert_batch('role_permissions', $rows);
        if ($this->count_active_user_managers() < 1) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'last_admin'];
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'role_id' => $savedRoleId];
    }

    private function count_active_user_managers()
    {
        $row = $this->db->query("SELECT COUNT(*) AS total
            FROM users u
            JOIN roles r ON r.id = u.role_id AND r.is_active = 1
            JOIN permissions p ON p.permission_key = 'administration.users.manage'
            LEFT JOIN role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
            LEFT JOIN user_permission_overrides upo ON upo.user_id = u.id AND upo.permission_id = p.id
            WHERE u.is_active = 1
              AND (upo.effect = 'allow' OR (upo.effect IS NULL AND rp.permission_id IS NOT NULL))")->row();
        return (int) ($row->total ?? 0);
    }
}
