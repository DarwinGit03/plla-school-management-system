<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Administration extends MY_Controller
{
    private $page_size = 25;

    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole([1, 2]);
        $this->load->model('system/Administration_model', 'administration_model');
    }

    public function index()
    {
        return redirect('admin/users');
    }

    public function users()
    {
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

    public function roles()
    {
        $this->render_page('administration/roles', [
            'title' => 'Roles',
            'page_title' => 'Roles',
            'page_subtitle' => 'Review the roles assigned to user accounts.',
            'breadcrumb' => ['Administration', 'Roles'],
            'roles' => $this->administration_model->get_roles()
        ]);
    }

    public function audit_logs()
    {
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
}
