<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();
        $this->load->model('system/School_event_model', 'school_event_model');

    }

    public function index()
    {
        $canManageAccounts = $this->can('administration.users.manage');
        if (!$canManageAccounts
            && ($this->can('payroll.self.view') || $this->can('academic.subjects.view_own'))
            && !$this->can('payroll.manage')) {
            $this->load->model('teacher/teacher_model');
            $teacher = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
            $this->load->view('dashboard/layouts/master', array_merge($this->data, [
                'title' => 'Teacher Dashboard',
                'page_title' => 'Teacher Dashboard',
                'page_subtitle' => 'Your school workspace and teaching tools.',
                'breadcrumb' => ['Dashboard'],
                'content' => 'dashboard/teacher/dashboard',
                'teacher' => $teacher,
                'upcoming_events' => $this->school_event_model->get_upcoming(5),
                'page_styles' => ['assets/css/events.css']
            ]));
            return;
        }
        if (!$canManageAccounts && $this->can('parent.payments.view')) return redirect('parents/payment-history');
        if ($this->can('dashboard.view')) {
                $this->load->model('finance/Fee_configuration_model', 'fee_configuration_model');
                $this->load->model('system/Dashboard_model', 'dashboard_model');
                $schoolYears = $this->fee_configuration_model->get_active_school_years();
                $availableYears = array_map(function ($row) { return (string) $row->school_year; }, $schoolYears);
                $userId = (int) $this->session->userdata('user_id');
                $queryParams = $this->input->get(null, true);
                $hasYearSelection = is_array($queryParams) && array_key_exists('school_year', $queryParams);
                $requestedValue = $this->input->get('school_year', true);
                $requestedYear = is_scalar($requestedValue) ? trim((string) $requestedValue) : '';
                $savedYear = $this->dashboard_model->get_dashboard_school_year_preference($userId);
                if ($hasYearSelection && in_array($requestedYear, $availableYears, true)) {
                    $selectedYear = $requestedYear;
                    $this->dashboard_model->save_dashboard_school_year_preference($userId, $selectedYear);
                } elseif (in_array($savedYear, $availableYears, true)) {
                    $selectedYear = $savedYear;
                } else {
                    $selectedYear = $availableYears[0] ?? '';
                }
                $this->data['school_years'] = $schoolYears;
                $this->data['selected_school_year'] = $selectedYear;
                $this->data['active_student_count'] = $this->dashboard_model->count_active_students($selectedYear);
                $this->data['students_by_grade'] = $this->dashboard_model->get_active_students_by_grade($selectedYear);
                $this->data['active_employee_count'] = $this->dashboard_model->count_active_employees();
                $this->data['upcoming_events'] = $this->school_event_model->get_upcoming(5);
                $this->data['title'] = 'Administrator Dashboard';

                $this->data['page_title'] = 'Administrator Dashboard';

                $this->data['page_subtitle'] = 'School Management System';
                $this->data['page_styles'] = ['assets/css/admin-dashboard.css', 'assets/css/events.css'];

                $this->data['breadcrumb'] = [
                    'Dashboard'
                ];

                $this->data['content'] = 'dashboard/admin/dashboard';

                $this->load->view(
                    'dashboard/layouts/master',
                    $this->data
                );

        } elseif ($this->can('payroll.manage') || $this->can('finance.fees.manage') || $this->can('finance.payments.manage')) {
            return redirect($this->can('payroll.manage') ? 'payroll' : 'finance');
        } else {
            show_error('Your account does not have access to a dashboard.', 403, 'Forbidden');
        }

    }

}
