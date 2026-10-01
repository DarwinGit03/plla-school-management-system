<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Administration extends MY_Controller
{
    private $page_size = 25;

    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->library('form_validation');
        $this->load->library('password_validator');
        $this->load->model('system/Administration_model', 'administration_model');
        $this->load->model('rbac/Permission_model', 'permission_model');
    }

    public function index()
    {
        return redirect('admin/users');
    }

    public function users()
    {
        $this->requirePermission('administration.users.manage');
        $search = trim((string) $this->input->get('q', true));
        $role_id = trim((string) $this->input->get('role', true));
        $status = trim((string) $this->input->get('status', true));
        if (!in_array($status, ['', 'active', 'inactive'], true)) $status = '';
        $page = max(1, (int) $this->input->get('page'));
        $total = $this->administration_model->count_users($search, $role_id, $status);
        $this->render_page('administration/users', [
            'title' => 'User accounts',
            'page_title' => 'User accounts',
            'page_subtitle' => 'Review system accounts and their assigned roles.',
            'breadcrumb' => ['Administration', 'Users'],
            'users' => $this->administration_model->get_users($search, $role_id, $status, $this->page_size, ($page - 1) * $this->page_size),
            'roles' => $this->administration_model->get_roles(),
            'search' => $search,
            'role_filter' => $role_id,
            'status_filter' => $status,
            'current_page' => $page,
            'total_pages' => max(1, (int) ceil($total / $this->page_size)),
            'total_users' => $total
        ]);
    }

    public function user_create()
    {
        $this->requirePermission('administration.users.manage');
        $this->load->model('teacher/teacher_model');
        $this->render_page('administration/user_create', [
            'title' => 'Create user account',
            'page_title' => 'Create user account',
            'page_subtitle' => 'Create a login, assign its role, then configure individual access.',
            'breadcrumb' => ['Administration', 'Users', 'Create account'],
            'roles' => $this->administration_model->get_active_roles(),
            'teacher_role_id' => $this->teacher_role_id(),
            'employees' => $this->teacher_model->get_unlinked_employees(),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function save_user()
    {
        $this->requirePermission('administration.users.manage');
        if ($this->input->method() !== 'post') { show_404(); return; }

        $role = $this->administration_model->get_active_role((int) $this->input->post('role_id', true));
        if (!$role) {
            $this->session->set_flashdata('error', 'Choose an active role for this account.');
            return redirect('admin/users/create');
        }
        $isEmployee = strtolower($role->role_name) === 'teacher';
        if ($isEmployee) {
            $this->form_validation->set_rules('employee_id', 'Employee profile', 'trim|required|integer');
        } else {
            $this->form_validation->set_rules('first_name', 'First name', 'trim|required|max_length[100]');
            $this->form_validation->set_rules('middle_name', 'Middle name', 'trim|max_length[100]');
            $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[150]');
            $this->form_validation->set_rules('employee_no', 'Employee / reference number', 'trim|required|max_length[100]');
        }
        $this->form_validation->set_rules('username', 'Username', 'trim|required|alpha_dash|max_length[100]');
        $this->form_validation->set_rules('role_id', 'Role', 'trim|required|integer');
        $this->form_validation->set_rules('password', 'Initial password', 'required|callback_password_policy');
        $this->form_validation->set_rules('password_confirm', 'Confirm password', 'required|max_length[72]|matches[password]');

        if (!$this->form_validation->run()) {
            return $this->render_page('administration/user_create', [
                'title' => 'Create user account',
                'page_title' => 'Create user account',
                'page_subtitle' => 'Create a login, assign its role, then configure individual access.',
                'breadcrumb' => ['Administration', 'Users', 'Create account'],
                'roles' => $this->administration_model->get_active_roles(),
                'teacher_role_id' => $this->teacher_role_id(),
                'employees' => $this->teacher_model_for_create()->get_unlinked_employees(),
                'error_message' => strip_tags(validation_errors(' ', ' '))
            ]);
        }

        $result = $this->administration_model->create_user([
            'first_name' => $this->input->post('first_name', true),
            'middle_name' => $this->input->post('middle_name', true),
            'last_name' => $this->input->post('last_name', true),
            'username' => $this->input->post('username', true),
            'email' => $this->input->post('email', true),
            'employee_no' => $this->input->post('employee_no', true),
            'employee_id' => (int) $this->input->post('employee_id', true),
            'role_id' => (int) $this->input->post('role_id', true),
            'password' => (string) $this->input->post('password')
        ]);
        if (!$result['ok']) {
            $messages = [
                'invalid_role' => 'Choose an active role for this account.',
                'duplicate_username' => 'That username is already in use.',
                'duplicate_email' => 'That email is already in use.',
                'duplicate_employee_no' => 'That employee number is already in use.'
                ,'invalid_employee' => 'Select an active employee without a login account.'
                ,'employee_email_missing' => 'Add a work email to this employee profile before creating the login.'
            ];
            return $this->render_page('administration/user_create', [
                'title' => 'Create user account',
                'page_title' => 'Create user account',
                'page_subtitle' => 'Create a login, assign its role, then configure individual access.',
                'breadcrumb' => ['Administration', 'Users', 'Create account'],
                'roles' => $this->administration_model->get_active_roles(),
                'teacher_role_id' => $this->teacher_role_id(),
                'employees' => $this->teacher_model_for_create()->get_unlinked_employees(),
                'error_message' => $messages[$result['code']] ?? 'The account could not be created.'
            ]);
        }

        $this->audit_log_service->log('administration', 'USER_CREATED', $result['user_id'], 'User account created.', ['role_id' => (int) $this->input->post('role_id', true)]);
        $this->session->set_flashdata('success', 'Account created. Configure individual permissions below.');
        if (strtolower($result['role_name']) === 'teacher') {
            $this->session->set_flashdata('success', 'Employee login created and linked to the existing profile.');
        }
        return redirect('admin/users/' . $result['user_id'] . '/access');
    }

    public function roles()
    {
        $this->requirePermission('administration.roles.manage');
        $this->render_page('administration/roles', [
            'title' => 'Roles',
            'page_title' => 'Roles',
            'page_subtitle' => 'Review the roles assigned to user accounts.',
            'breadcrumb' => ['Administration', 'Roles'],
            'roles' => $this->administration_model->get_roles(),
            'permissions' => $this->permission_model->get_catalog()
        ]);
    }

    public function user_access($user_id)
    {
        $this->requirePermission('administration.users.manage');
        $access = $this->permission_model->get_user_access((int) $user_id);
        if (!$access) { show_404(); return; }
        $teacher_profile_exists = false;
        if (strtolower((string) $access['user']->role_name) === 'teacher') {
            $this->load->model('teacher/teacher_model');
            $teacher_profile_exists = (bool) $this->teacher_model->get_by_user_id((int) $user_id);
        }
        $this->render_page('administration/user_access', [
            'title' => 'User access',
            'page_title' => 'User access',
            'page_subtitle' => 'Assign a role and manage this account’s permission overrides.',
            'breadcrumb' => ['Administration', 'Users', 'User Access'],
            'access' => $access,
            'roles' => $this->administration_model->get_roles(),
            'current_user_id' => (int) $this->session->userdata('user_id'),
            'teacher_profile_exists' => $teacher_profile_exists,
            'error_message' => $this->session->flashdata('error'),
            'success_message' => $this->session->flashdata('success')
        ]);
    }

    public function save_user_access($user_id)
    {
        $this->requirePermission('administration.users.manage');
        if ($this->input->method() !== 'post') { show_404(); return; }
        if ((int) $user_id === (int) $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'You cannot change your own role or permissions.');
            return redirect('admin/users/' . (int) $user_id . '/access');
        }
        $role_id = (int) $this->input->post('role_id', true);
        $overrides = $this->input->post('permission');
        $overrides = is_array($overrides) ? $overrides : [];
        $reason = trim((string) $this->input->post('reason', true));
        $result = $this->permission_model->save_user_access(
            (int) $user_id,
            $role_id,
            $overrides,
            (string) $this->session->userdata('employee_no'),
            $reason
        );
        if (!$result['ok']) {
            $message = $result['code'] === 'last_admin'
                ? 'At least one active user must retain administration access.'
                : 'The role and user permissions could not be saved. Check the selected role and permission values.';
            $this->session->set_flashdata('error', $message);
            return redirect('admin/users/' . (int) $user_id . '/access');
        }
        $this->audit_log_service->log('administration', 'USER_ACCESS_UPDATED', (int) $user_id, 'User role and permission overrides updated.', ['role_id' => $role_id, 'overrides' => array_filter($overrides, function ($effect) { return $effect !== 'inherit'; }), 'reason' => $reason]);
        $this->session->set_flashdata('success', 'User access updated.');
        return redirect('admin/users/' . (int) $user_id . '/access');
    }

    public function role_edit($role_id)
    {
        $this->requirePermission('administration.roles.manage');
        $access = $this->permission_model->get_role_access((int) $role_id);
        if (!$access) { show_404(); return; }
        $this->render_page('administration/role_form', [
            'title' => 'Edit role',
            'page_title' => 'Edit role',
            'page_subtitle' => 'Choose which permissions this role grants.',
            'breadcrumb' => ['Administration', 'Roles', 'Edit'],
            'access' => $access,
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function role_create()
    {
        $this->requirePermission('administration.roles.manage');
        $permissions = $this->permission_model->get_catalog();
        foreach ($permissions as $permission) {
            $permission->granted = false;
        }
        $this->render_page('administration/role_form', [
            'title' => 'Create role',
            'page_title' => 'Create role',
            'page_subtitle' => 'Create a reusable role and select its permissions.',
            'breadcrumb' => ['Administration', 'Roles', 'New Role'],
            'access' => ['role' => (object) ['id' => 0, 'role_name' => '', 'description' => '', 'is_active' => 1, 'is_system' => 0], 'permissions' => $permissions],
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function save_role()
    {
        $this->requirePermission('administration.roles.manage');
        if ($this->input->method() !== 'post') { show_404(); return; }
        $role_id = (int) $this->input->post('role_id', true);
        $permissions = $this->input->post('permission_ids');
        $permissions = is_array($permissions) ? $permissions : [];
        $result = $this->permission_model->save_role_access(
            $role_id,
            $this->input->post('role_name', true),
            $this->input->post('description', true),
            $this->input->post('is_active', true) === '1',
            $permissions
        );
        if (!$result['ok']) {
            $message = $result['code'] === 'system_role_protected'
                ? 'The built-in Admin role must stay active with all permissions.'
                : ($result['code'] === 'last_admin'
                    ? 'At least one active user must retain administration access.'
                    : 'The role could not be saved. Check the name and selected permissions.');
            $this->session->set_flashdata('error', $message);
            return redirect($role_id > 0 ? 'admin/roles/' . $role_id . '/edit' : 'admin/roles/create');
        }
        $this->audit_log_service->log('administration', $role_id > 0 ? 'ROLE_UPDATED' : 'ROLE_CREATED', $result['role_id'], 'Role and permission grants saved.', ['role_name' => trim((string) $this->input->post('role_name', true)), 'permission_ids' => array_values(array_unique(array_map('intval', $permissions)))]);
        $this->session->set_flashdata('success', 'Role permissions saved.');
        return redirect('admin/roles');
    }

    public function audit_logs()
    {
        $this->requirePermission('administration.audit.view');
        $search = trim((string) $this->input->get('q', true));
        $page = max(1, (int) $this->input->get('page'));
        $total = $this->administration_model->count_audit_logs($search);
        $this->render_page('administration/audit_logs', [
            'title' => 'Audit logs',
            'page_title' => 'Audit logs',
            'page_subtitle' => 'Review recorded account and system activity.',
            'breadcrumb' => ['Administration', 'Audit Logs'],
            'logs' => $this->administration_model->get_audit_logs($search, $this->page_size, ($page - 1) * $this->page_size),
            'search' => $search,
            'current_page' => $page,
            'total_pages' => max(1, (int) ceil($total / $this->page_size)),
            'total_logs' => $total
        ]);
    }

    public function settings()
    {
        $this->requirePermission('administration.settings.manage');
        $this->render_page('administration/settings', [
            'title' => 'System settings',
            'page_title' => 'System settings',
            'page_subtitle' => 'Manage the configuration areas currently available in the system.',
            'breadcrumb' => ['Administration', 'Settings'],
            'overview' => $this->administration_model->get_overview()
        ]);
    }

    private function render_page($content, array $data)
    {
        $data['content'] = $content;
        $this->load->view('dashboard/layouts/master', $data);
    }

    private function teacher_role_id()
    {
        foreach ($this->administration_model->get_active_roles() as $role) {
            if (strtolower($role->role_name) === 'teacher') return (int) $role->id;
        }
        return 0;
    }

    private function teacher_model_for_create()
    {
        $this->load->model('teacher/teacher_model');
        return $this->teacher_model;
    }

    public function password_policy($password)
    {
        $errors = $this->password_validator->validate($password);
        if (!$errors) return true;
        $this->form_validation->set_message('password_policy', $this->password_validator->error_message($errors));
        return false;
    }
}
