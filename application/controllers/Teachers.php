<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Teachers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole([1, 2, 4]);

        $this->load->library('form_validation');
        $this->load->model('teacher/teacher_model');
    }

    public function index()
    {
        $status = trim((string) $this->input->get('status', true));
        if (!in_array($status, ['', 'active', 'inactive'], true)) {
            $status = '';
        }

        $this->render_page('teachers/index', [
            'title' => 'Faculty Directory',
            'page_title' => 'Faculty Directory',
            'page_subtitle' => 'Manage teacher profiles and employment details.',
            'breadcrumb' => ['Faculty', 'Faculty Directory'],
            'teachers' => $this->teacher_model->get_teachers([
                'search' => trim((string) $this->input->get('q', true)),
                'status' => $status
            ]),
            'search' => trim((string) $this->input->get('q', true)),
            'selected_status' => $status,
            'can_manage_profiles' => in_array((int) $this->session->userdata('role_id'), [1, 2], true),
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function create()
    {
        $this->requireRole([1, 2]);
        $this->render_form(null, 'Register teacher');
    }

    public function edit($id)
    {
        $this->requireRole([1, 2]);
        $teacher = $this->teacher_model->get_by_id($id);
        if (!$teacher) {
            show_404();
            return;
        }

        $this->render_form($teacher, 'Edit teacher');
    }

    public function save()
    {
        $this->requireRole([1, 2]);
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $id = (int) $this->input->post('id', true);
        $teacher = $id > 0 ? $this->teacher_model->get_by_id($id) : null;
        if ($id > 0 && !$teacher) {
            show_404();
            return;
        }

        $this->form_validation->set_rules('employee_no', 'Employee number', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('first_name', 'First name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('middle_name', 'Middle name', 'trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('suffix', 'Suffix', 'trim|max_length[30]');
        $this->form_validation->set_rules('work_email', 'Work email', 'trim|valid_email|max_length[150]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[40]');
        $this->form_validation->set_rules('position_title', 'Position', 'trim|max_length[100]');
        $this->form_validation->set_rules('employment_type', 'Employment type', 'trim|required|in_list[full_time,part_time,contractual]');
        $this->form_validation->set_rules('hire_date', 'Hire date', 'trim|callback_valid_hire_date');
        $this->form_validation->set_rules('linked_user_id', 'Teacher login account', 'trim|integer');
        if ($id > 0) {
            $this->form_validation->set_rules('status', 'Status', 'trim|required|in_list[active,inactive]');
        } else {
            $this->form_validation->set_rules('salary_type', 'Salary basis', 'trim|required|in_list[monthly,daily]');
            $this->form_validation->set_rules('pay_frequency', 'Pay schedule', 'trim|required|in_list[weekly,semi_monthly]');
            $this->form_validation->set_rules('salary_amount', 'Salary rate', 'trim|required|numeric|greater_than[0]');
            $this->form_validation->set_rules('salary_effective_from', 'Salary effective date', 'trim|required|callback_valid_effective_date');
            $this->form_validation->set_rules('salary_change_reason', 'Salary change reason', 'trim|required|max_length[255]');
        }

        if (!$this->form_validation->run()) {
            return $this->render_form($teacher, $id > 0 ? 'Edit teacher' : 'Register teacher');
        }

        $employeeNo = trim((string) $this->input->post('employee_no', true));
        if ($this->teacher_model->employee_no_exists($employeeNo, $id)) {
            $this->session->set_flashdata('error', 'That employee number is already in use.');
            return redirect($id > 0 ? 'teachers/edit/' . $id : 'teachers/create');
        }

        $actor = (string) $this->session->userdata('employee_no');
        $data = [
            'employee_no' => $employeeNo,
            'first_name' => trim((string) $this->input->post('first_name', true)),
            'middle_name' => $this->nullable_post('middle_name'),
            'last_name' => trim((string) $this->input->post('last_name', true)),
            'suffix' => $this->nullable_post('suffix'),
            'work_email' => $this->nullable_post('work_email'),
            'phone' => $this->nullable_post('phone'),
            'position_title' => $this->nullable_post('position_title'),
            'employment_type' => trim((string) $this->input->post('employment_type', true)),
            'hire_date' => $this->nullable_post('hire_date'),
            'user_id' => (int) $this->input->post('linked_user_id', true) ?: null,
            'updated_by' => $actor
        ];

        if ($id === 0) {
            $data['status'] = 'active';
            $data['created_by'] = $actor;
        } else {
            $data['status'] = trim((string) $this->input->post('status', true));
        }

        $salaryType = '';
        $payFrequency = '';
        $salaryId = 0;
        if ($id === 0) {
            $salaryType = trim((string) $this->input->post('salary_type', true));
            $payFrequency = trim((string) $this->input->post('pay_frequency', true));
            $result = $this->teacher_model->register_with_salary(
                $data,
                $salaryType,
                $payFrequency,
                (float) $this->input->post('salary_amount', true),
                trim((string) $this->input->post('salary_effective_from', true)),
                trim((string) $this->input->post('salary_change_reason', true))
            );
            $saved = !empty($result['ok']) ? (int) $result['teacher_id'] : false;
            $salaryId = !empty($result['salary_id']) ? (int) $result['salary_id'] : 0;
        } else {
            $saved = $this->teacher_model->save($data, $id);
        }
        if (!$saved) {
            $this->session->set_flashdata('error', 'The teacher profile could not be saved. Check that the selected login has the Teacher role and is not linked to another profile.');
            return redirect($id > 0 ? 'teachers/edit/' . $id : 'teachers/create');
        }

        $recordId = $id > 0 ? $id : (int) $saved;
        $this->audit_log_service->log(
            'teachers',
            $id > 0 ? 'TEACHER_UPDATED' : 'TEACHER_CREATED',
            $recordId,
            $id > 0 ? 'Teacher profile updated.' : 'Teacher profile created.',
            [
                'employee_no' => $employeeNo,
                'position_title' => $data['position_title'],
                'employment_type' => $data['employment_type'],
                'status' => $data['status']
            ]
        );

        if ($id === 0 && $salaryId > 0) {
            $this->audit_log_service->log(
                'teacher_salary_history',
                'TEACHER_SALARY_RATE_ADDED',
                $salaryId,
                'Initial teacher salary rate added during registration.',
                ['teacher_id' => $recordId, 'salary_type' => $salaryType, 'pay_frequency' => $payFrequency, 'change_reason' => trim((string) $this->input->post('salary_change_reason', true))]
            );
        }

        $this->session->set_flashdata('success', $id > 0 ? 'Teacher profile updated.' : 'Teacher and starting salary registered.');
        return redirect('teachers');
    }

    public function salary($id)
    {
        $teacher = $this->teacher_model->get_by_id($id);
        if (!$teacher) {
            show_404();
            return;
        }

        $this->render_page('teachers/salary', [
            'title' => 'Salary Setup',
            'page_title' => 'Salary Setup',
            'page_subtitle' => 'Set a monthly or daily salary rate and keep its effective-dated history.',
            'breadcrumb' => ['Faculty', 'Faculty Directory', 'Salary Setup'],
            'teacher' => $teacher,
            'salary_history' => $this->teacher_model->get_salary_history($teacher->id),
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function save_salary()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $teacherId = (int) $this->input->post('teacher_id', true);
        $teacher = $teacherId > 0 ? $this->teacher_model->get_by_id($teacherId) : null;
        if (!$teacher) {
            show_404();
            return;
        }

        $this->form_validation->set_rules('salary_type', 'Salary basis', 'trim|required|in_list[monthly,daily]');
        $this->form_validation->set_rules('pay_frequency', 'Pay schedule', 'trim|required|in_list[weekly,semi_monthly]');
        $this->form_validation->set_rules('salary_amount', 'Salary amount', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('effective_from', 'Effective date', 'trim|required|callback_valid_effective_date');
        $this->form_validation->set_rules('change_reason', 'Change reason', 'trim|required|max_length[255]');
        if (!$this->form_validation->run()) {
            return $this->render_page('teachers/salary', [
                'title' => 'Salary Setup',
                'page_title' => 'Salary Setup',
                'page_subtitle' => 'Set a monthly or daily salary rate and keep its effective-dated history.',
                'breadcrumb' => ['Faculty', 'Faculty Directory', 'Salary Setup'],
                'teacher' => $teacher,
                'salary_history' => $this->teacher_model->get_salary_history($teacher->id),
                'error_message' => ''
            ]);
        }

        $salaryType = trim((string) $this->input->post('salary_type', true));
        $payFrequency = trim((string) $this->input->post('pay_frequency', true));
        $amount = (float) $this->input->post('salary_amount', true);
        $effectiveFrom = trim((string) $this->input->post('effective_from', true));
        $result = $this->teacher_model->add_salary_rate(
            $teacher->id,
            $salaryType,
            $payFrequency,
            $amount,
            $effectiveFrom,
            (string) $this->session->userdata('employee_no'),
            trim((string) $this->input->post('change_reason', true))
        );

        if (!$result['ok']) {
            $message = $result['code'] === 'date_order'
                ? 'A new salary rate must start after the latest saved effective date.'
                : 'The salary rate could not be saved.';
            $this->session->set_flashdata('error', $message);
            return redirect('teachers/salary/' . $teacher->id);
        }

        $this->audit_log_service->log(
            'teacher_salary_history',
            'TEACHER_SALARY_RATE_ADDED',
            $result['id'],
            'Teacher salary rate added.',
            ['teacher_id' => (int) $teacher->id, 'salary_type' => $salaryType, 'pay_frequency' => $payFrequency, 'effective_from' => $effectiveFrom, 'change_reason' => trim((string) $this->input->post('change_reason', true))]
        );
        $this->session->set_flashdata('success', ucfirst($salaryType) . ' salary rate saved.');
        return redirect('payroll');
    }

    public function change_status()
    {
        $this->requireRole([1, 2]);
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $id = (int) $this->input->post('id', true);
        $status = trim((string) $this->input->post('status', true));
        $teacher = $id > 0 ? $this->teacher_model->get_by_id($id) : null;
        if (!$teacher || !in_array($status, ['active', 'inactive'], true)) {
            show_404();
            return;
        }

        $saved = $this->teacher_model->update_status(
            $id,
            $status,
            (string) $this->session->userdata('employee_no')
        );

        if (!$saved) {
            $this->session->set_flashdata('error', 'Teacher status could not be updated.');
        } else {
            $this->audit_log_service->log(
                'teachers',
                'TEACHER_STATUS_CHANGED',
                $id,
                'Teacher status changed.',
                ['from' => $teacher->status, 'to' => $status]
            );
            $this->session->set_flashdata('success', 'Teacher status updated.');
        }

        return redirect('teachers');
    }

    public function valid_hire_date($value)
    {
        if ($value === '') return true;
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        $valid = $date && $date->format('Y-m-d') === $value;
        if (!$valid) {
            $this->form_validation->set_message('valid_hire_date', 'Enter a valid hire date.');
        }
        return $valid;
    }

    public function valid_effective_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        $valid = $date && $date->format('Y-m-d') === $value;
        if (!$valid) {
            $this->form_validation->set_message('valid_effective_date', 'Enter a valid effective date.');
        }
        return $valid;
    }

    private function nullable_post($field)
    {
        $value = trim((string) $this->input->post($field, true));
        return $value === '' ? null : $value;
    }

    private function render_form($teacher, $heading)
    {
        $this->render_page('teachers/form', [
            'title' => $heading,
            'page_title' => $heading,
            'page_subtitle' => 'Teacher profiles are managed separately from login accounts.',
            'breadcrumb' => ['Faculty', 'Faculty Directory', $heading],
            'teacher' => $teacher,
            'user_accounts' => $this->teacher_model->get_linkable_user_accounts($teacher ? $teacher->id : 0),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    private function render_page($content, array $data)
    {
        $this->load->view('dashboard/layouts/master', array_merge($data, [
            'content' => $content
        ]));
    }
}
