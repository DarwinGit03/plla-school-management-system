<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Administration_model extends CI_Model
{
    public function count_users($search = '', $role_id = '', $status = '')
    {
        $this->apply_user_filters($search, $role_id, $status);
        return (int) $this->db->count_all_results('users AS users');
    }

    public function get_users($search = '', $role_id = '', $status = '', $limit = 25, $offset = 0)
    {
        $this->apply_user_filters($search, $role_id, $status);
        return $this->db
            ->select('users.id, users.username, users.email, users.first_name, users.middle_name, users.last_name, users.role_id, users.employee_no, users.is_active, users.last_login, roles.role_name')
            ->from('users AS users')
            ->join('roles AS roles', 'roles.id = users.role_id', 'left')
            ->order_by('users.last_name', 'ASC')
            ->order_by('users.first_name', 'ASC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    private function apply_user_filters($search, $role_id, $status)
    {
        if ($search !== '') {
            $this->db->group_start()
                ->like('users.username', $search)
                ->or_like('users.email', $search)
                ->or_like('users.first_name', $search)
                ->or_like('users.last_name', $search)
                ->or_like('users.employee_no', $search)
                ->group_end();
        }
        if ($role_id !== '' && ctype_digit((string) $role_id)) $this->db->where('users.role_id', (int) $role_id);
        if ($status === 'active') $this->db->where('users.is_active', 1);
        if ($status === 'inactive') $this->db->where('users.is_active', 0);
    }

    public function get_roles()
    {
        return $this->db
            ->select('roles.id, roles.role_name, roles.description, roles.is_active, roles.is_system, COUNT(DISTINCT users.id) AS user_count, (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = roles.id) AS permission_count', false)
            ->from('roles AS roles')
            ->join('users AS users', 'users.role_id = roles.id', 'left')
            ->group_by(['roles.id', 'roles.role_name', 'roles.description', 'roles.is_active', 'roles.is_system'])
            ->order_by('roles.id', 'ASC')
            ->get()
            ->result();
    }

    public function get_active_roles()
    {
        return $this->db->where('is_active', 1)->order_by('role_name', 'ASC')->get('roles')->result();
    }

    public function get_active_role($role_id)
    {
        return $this->db
            ->where(['id' => (int) $role_id, 'is_active' => 1])
            ->get('roles')
            ->row();
    }

    public function create_user(array $data)
    {
        $role = $this->get_active_role($data['role_id']);
        if (!$role) return ['ok' => false, 'code' => 'invalid_role'];
        $isEmployee = strtolower($role->role_name) === 'teacher';
        $this->db->trans_begin();
        if ($isEmployee) {
            $employee = $this->db->query(
                'SELECT id, employee_no, first_name, middle_name, last_name, work_email FROM employees WHERE id = ? AND status = ? AND user_id IS NULL FOR UPDATE',
                [(int) ($data['employee_id'] ?? 0), 'active']
            )->row();
            if (!$employee) {
                $this->db->trans_rollback();
                return ['ok' => false, 'code' => 'invalid_employee'];
            }
            if (empty($employee->work_email)) {
                $this->db->trans_rollback();
                return ['ok' => false, 'code' => 'employee_email_missing'];
            }
            $data = array_merge($data, [
                'email' => $employee->work_email,
                'employee_no' => $employee->employee_no,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name
            ]);
        }
        foreach (['username', 'email'] as $identityField) {
            $identity = strtolower(trim((string) $data[$identityField]));
            $matchesExistingIdentity = $this->db
                ->group_start()
                    ->where('username', $identity)
                    ->or_where('email', $identity)
                ->group_end()
                ->count_all_results('users');
            if ($matchesExistingIdentity > 0) {
                $this->db->trans_rollback();
                return ['ok' => false, 'code' => 'duplicate_' . $identityField];
            }
        }
        if ($this->db->where('employee_no', trim((string) $data['employee_no']))->count_all_results('users') > 0) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'duplicate_employee_no'];
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $insert = [
            'username' => trim($data['username']),
            'email' => strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => trim($data['first_name']),
            'middle_name' => trim((string) ($data['middle_name'] ?? '')) ?: null,
            'last_name' => trim($data['last_name']),
            'role_id' => (int) $data['role_id'],
            'is_active' => 1,
            'employee_no' => trim($data['employee_no']),
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (!$this->db->insert('users', $insert) || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $id = (int) $this->db->insert_id();
        if ($isEmployee && !$this->db->where(['id' => (int) $employee->id, 'user_id' => null])->update('employees', ['user_id' => $id])) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'user_id' => $id, 'role_name' => $role->role_name, 'employee_id' => $isEmployee ? (int) $employee->id : null];
    }

    public function count_audit_logs($search = '')
    {
        $this->apply_audit_search($search);
        return (int) $this->db->count_all_results('audit_logs AS logs');
    }

    public function get_audit_logs($search = '', $limit = 25, $offset = 0)
    {
        $this->apply_audit_search($search);
        return $this->db
            ->select('logs.id, logs.user_id, logs.employee_no, logs.module, logs.action, logs.record_id, logs.description, logs.details, logs.ip_address, logs.created_at, users.first_name, users.last_name')
            ->from('audit_logs AS logs')
            ->join('users AS users', 'users.id = logs.user_id', 'left')
            ->order_by('logs.id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    private function apply_audit_search($search)
    {
        if ($search === '') return;
        $this->db->group_start()
            ->like('logs.module', $search)
            ->or_like('logs.action', $search)
            ->or_like('logs.description', $search)
            ->or_like('logs.employee_no', $search)
            ->or_like('logs.ip_address', $search)
            ->group_end();
    }

    public function get_overview()
    {
        return [
            'users' => (int) $this->db->count_all('users'),
            'active_users' => (int) $this->db->where('is_active', 1)->count_all_results('users'),
            'roles' => (int) $this->db->count_all('roles'),
            'audit_events' => (int) $this->db->count_all('audit_logs')
        ];
    }
}
