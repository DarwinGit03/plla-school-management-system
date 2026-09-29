<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();
        $this->requireRole([1, 2, 5]);

        $this->load->model(
            'finance/fee_configuration_model'
        );
        $this->load->model('finance/payment_model');
    }

    public function index()
    {
        $years = $this->fee_configuration_model->get_school_years();
        $requested_year = trim((string) $this->input->get('school_year', true));
        $year_values = array_map(function ($year) { return (string) $year->school_year; }, $years);
        $school_year = in_array($requested_year, $year_values, true) ? $requested_year : '';
        $selected_year_deletion = $school_year !== ''
            ? $this->fee_configuration_model->check_draft_school_year_deletable($school_year)
            : ['ok' => false, 'message' => ''];
        $configurations = $this->fee_configuration_model->get_configurations_by_year($school_year);
        $year_statuses = array_unique(array_map(function ($configuration) { return $configuration->status; }, $configurations));
        $selected_year_status = count($year_statuses) === 1 ? reset($year_statuses) : '';
        $this->load->view('dashboard/layouts/master', [
            'title' => 'Fee Configurations',
            'page_title' => 'Fee Configurations',
            'page_subtitle' => 'Review school year, grade, payment mode, and fee configuration status.',
            'breadcrumb' => ['Finance', 'Fee Configurations'],
            'content' => 'finance/configurations/index',
            'page_scripts' => ['assets/js/finance/configurations.js'],
            'configurations' => $configurations,
            'school_years' => $years,
            'year_inventory' => $this->fee_configuration_model->get_fee_year_inventory(),
            'selected_school_year' => $school_year,
            'selected_year_deletable' => $selected_year_deletion['ok'],
            'selected_year_status' => $selected_year_status
        ]);
    }

    public function edit_configuration($configuration_id)
    {
        $configuration = $this->fee_configuration_model->get_configuration_by_id((int) $configuration_id);
        if (!$configuration) { show_404(); return; }
        if ($this->input->method() === 'post') {
            $saved = $this->fee_configuration_model->update_configuration_schedule((int) $configuration_id, [
                'status' => $this->input->post('status', true),
                'fees' => $this->input->post('fees'),
                'uniforms' => $this->input->post('uniforms')
            ]);
            $this->session->set_flashdata($saved ? 'success' : 'error', $saved
                ? 'Fee configuration updated.'
                : 'The configuration could not be updated. Check the submitted fee values.');
            return redirect('finance/view/' . (int) $configuration_id);
        }
        $this->load->view('dashboard/layouts/master', [
            'title' => 'Edit Fee Configuration',
            'page_title' => 'Edit Fee Configuration',
            'page_subtitle' => 'Update the fee schedule for this school year and grade.',
            'breadcrumb' => ['Finance', 'Fee Configurations', 'Edit'],
            'content' => 'finance/configurations/edit',
            'configuration' => $configuration,
            'fees' => $this->fee_configuration_model->get_editable_fees($configuration)
        ]);
    }

    public function duplicate_school_year()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $source_year = trim((string) $this->input->post('source_school_year', true));
        $target_year = trim((string) $this->input->post('target_school_year', true));
        $configuration_status = trim((string) $this->input->post('configuration_status', true));
        if (!preg_match('/^\d{4}-\d{4}$/', $source_year) || !preg_match('/^\d{4}-\d{4}$/', $target_year)) {
            $result = ['ok' => false, 'message' => 'Choose valid source and target school years.'];
        } else {
            $result = $this->fee_configuration_model->duplicate_school_year($source_year, $target_year, $configuration_status);
        }
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect('finance?school_year=' . rawurlencode($result['ok'] ? $target_year : $source_year));
    }

    public function delete_school_year()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $school_year = trim((string) $this->input->post('school_year', true));
        if ($this->input->post('confirm_delete') !== '1') {
            $result = ['ok' => false, 'message' => 'Confirm the deletion before continuing.'];
        } elseif (!preg_match('/^\d{4}-\d{4}$/', $school_year)) {
            $result = ['ok' => false, 'message' => 'Choose a valid school year.'];
        } else {
            $result = $this->fee_configuration_model->delete_draft_school_year($school_year);
        }
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect('finance' . ($result['ok'] ? '' : '?school_year=' . rawurlencode($school_year)));
    }

    public function update_school_year_status()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $school_year = trim((string) $this->input->post('school_year', true));
        $status = trim((string) $this->input->post('status', true));
        if (!preg_match('/^\d{4}-\d{4}$/', $school_year)) {
            $result = ['ok' => false, 'message' => 'Choose a valid school year.'];
        } else {
            $result = $this->fee_configuration_model->update_school_year_status($school_year, $status);
        }
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect('finance?school_year=' . rawurlencode($school_year));
    }

    public function view($configuration_id)
    {
        $configuration = $this->fee_configuration_model
            ->get_configuration_by_id((int) $configuration_id);

        if (!$configuration) {
            show_404();
            return;
        }

        $this->load->view('dashboard/layouts/master', [
            'title' => 'Fee Configuration Details',
            'page_title' => 'Fee Configuration Details',
            'page_subtitle' => 'Review the fee schedule for this configuration.',
            'breadcrumb' => ['Finance', 'Fee Configurations', 'Details'],
            'content' => 'finance/configurations/view',
            'configuration' => $configuration,
            'fees' => $this->fee_configuration_model->get_complete_fees(
                $configuration->school_year,
                $configuration->grade_code,
                $configuration->payment_mode
            )
        ]);
    }

    /** Search unpaid assessed charges and record a finance payment. */
    public function payments()
    {
        $search = trim((string) $this->input->get('search', true));
        $student_id = (int) $this->input->get('student_id');
        $school_years = $this->fee_configuration_model->get_school_years();
        $school_year_values = array_map(function ($year) {
            return (string) $year->school_year;
        }, $school_years);
        $requested_year = trim((string) $this->input->get('academic_year', true));
        $academic_year = in_array($requested_year, $school_year_values, true)
            ? $requested_year
            : '';
        $grade_levels = $academic_year !== ''
            ? $this->fee_configuration_model->get_grade_codes_by_year($academic_year)
            : [];
        $grade_options_by_year = [];
        foreach ($school_years as $school_year_option) {
            $year_value = (string) $school_year_option->school_year;
            $year_grades = $year_value === $academic_year
                ? $grade_levels
                : $this->fee_configuration_model->get_grade_codes_by_year($year_value);
            $grade_options_by_year[$year_value] = array_map(function ($grade) {
                return (string) $grade->grade;
            }, $year_grades);
        }
        $grade_values = array_map(function ($grade) {
            return (string) $grade->grade;
        }, $grade_levels);
        $requested_grade = trim((string) $this->input->get('grade_level', true));
        $grade_level = in_array($requested_grade, $grade_values, true)
            ? $requested_grade
            : '';
        $this->load->library('Student_service');
        $sections = ($academic_year !== '' && $grade_level !== '')
            ? $this->student_service->get_sections_by_year_and_grade($academic_year, $grade_level)
            : [];
        $section_values = array_map(function ($section) {
            return (string) $section->section;
        }, $sections);
        $requested_section = trim((string) $this->input->get('section', true));
        $section = in_array($requested_section, $section_values, true)
            ? $requested_section
            : '';
        $has_search = $search !== '' || $academic_year !== '' || $grade_level !== '' || $section !== '';

        if ($this->input->method() === 'post') {
            $this->load->library('Payment_service');
            $result = $this->payment_service->record_payment(
                [
                    'enrollment_fee_id' => $this->input->post('enrollment_fee_id'),
                    'student_id' => $this->input->post('student_id'),
                    'amount_paid' => $this->input->post('amount_paid'),
                    'payment_method' => $this->input->post('payment_method'),
                    'reference_number' => $this->input->post('reference_number')
                ],
                $this->session->userdata('user_id')
            );

            $this->session->set_flashdata(
                $result['ok'] ? 'success' : 'error',
                $result['message']
            );

            $search = trim((string) $this->input->post('search', true));
            $student_id = (int) $this->input->post('student_id');
            $requested_year = trim((string) $this->input->post('academic_year', true));
            $academic_year = in_array($requested_year, $school_year_values, true)
                ? $requested_year
                : '';
            $requested_grade = trim((string) $this->input->post('grade_level', true));
            $available_grades = $academic_year !== ''
                ? $this->fee_configuration_model->get_grade_codes_by_year($academic_year)
                : [];
            $available_grade_values = array_map(function ($grade) {
                return (string) $grade->grade;
            }, $available_grades);
            $grade_level = in_array($requested_grade, $available_grade_values, true)
                ? $requested_grade
                : '';
            $requested_section = trim((string) $this->input->post('section', true));
            $available_sections = ($academic_year !== '' && $grade_level !== '')
                ? $this->student_service->get_sections_by_year_and_grade($academic_year, $grade_level)
                : [];
            $available_section_values = array_map(function ($section_record) {
                return (string) $section_record->section;
            }, $available_sections);
            $section = in_array($requested_section, $available_section_values, true)
                ? $requested_section
                : '';
            return redirect('finance/payments?' . http_build_query([
                'search' => $search,
                'student_id' => $student_id,
                'academic_year' => $academic_year,
                'grade_level' => $grade_level,
                'section' => $section
            ]));
        }

        $this->load->library('Payment_service');
        $selected_student = ($has_search && $student_id > 0)
            ? $this->payment_model->get_student_payment_summary($student_id, $academic_year)
            : null;
        $students = ($has_search && $student_id === 0)
            ? $this->payment_model->search_students_for_payments($search, $academic_year, $grade_level, $section)
            : [];
        $payment_history = $selected_student
            ? $this->payment_model->get_payment_history_by_student($student_id, $academic_year)
            : [];
        $payment_ids = array_map(function ($payment) { return (int) $payment->id; }, $payment_history);

        $this->load->view('dashboard/layouts/master', [
            'title' => 'Record Payments',
            'page_title' => 'Record Payments',
            'page_subtitle' => 'Post payments against saved enrollment fee assessments.',
            'breadcrumb' => ['Finance', 'Payments'],
            'content' => 'finance/payments/index',
            'search' => $search,
            'academic_year' => $academic_year,
            'school_years' => $school_years,
            'grade_level' => $grade_level,
            'grade_levels' => $grade_levels,
            'grade_options_by_year' => $grade_options_by_year,
            'section' => $section,
            'sections' => $sections,
            'has_search' => $has_search,
            'students' => $students,
            'selected_student' => $selected_student,
            'fee_balances' => $selected_student
                ? $this->payment_model->get_outstanding_fees_by_student($student_id, $academic_year)
                : [],
            'payment_history' => $payment_history,
            'payment_audit' => $this->payment_model->get_payment_audit_by_payment_ids($payment_ids),
            'student_id' => $student_id,
            'page_scripts' => ['assets/js/finance/finance.js?v=20260929'],
            'payment_methods' => $this->payment_service->get_payment_methods()
        ]);
    }

    public function void_payment()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $this->load->library('Payment_service');
        $student_id = (int) $this->input->post('student_id');
        $result = $this->payment_service->void_payment(
            $this->input->post('payment_id'),
            $student_id,
            $this->input->post('reason'),
            $this->session->userdata('user_id')
        );
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return $this->redirect_to_payment_student($student_id);
    }

    public function correct_payment()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $this->load->library('Payment_service');
        $student_id = (int) $this->input->post('student_id');
        $result = $this->payment_service->correct_payment_details(
            $this->input->post('payment_id'),
            $student_id,
            [
                'payment_method' => $this->input->post('payment_method'),
                'reference_number' => $this->input->post('reference_number'),
                'reason' => $this->input->post('reason')
            ],
            $this->session->userdata('user_id')
        );
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return $this->redirect_to_payment_student($student_id);
    }

    private function redirect_to_payment_student($student_id)
    {
        return redirect('finance/payments?' . http_build_query([
            'search' => trim((string) $this->input->post('search', true)),
            'student_id' => (int) $student_id,
            'academic_year' => trim((string) $this->input->post('academic_year', true)),
            'grade_level' => trim((string) $this->input->post('grade_level', true)),
            'section' => trim((string) $this->input->post('section', true))
        ]));
    }

    /** Download the selected student's payment statement as a PDF. */
    public function payment_statement($student_id, $display = 'attachment')
    {
        $academic_year = $this->normalize_payment_school_year($this->input->get('academic_year', true));
        $statement = $this->build_payment_statement((int) $student_id, $academic_year);
        if (!$statement) {
            show_404();
            return;
        }
        $this->load->library('Payment_statement_pdf');
        $pdf = $this->payment_statement_pdf->generate(
            $statement['student'],
            $statement['payments'],
            $statement['primary_guardian'],
            $this->payment_statement_signer()
        );
        $filename = $display === 'inline'
            ? 'Payment Record View.pdf'
            : $this->payment_statement_filename($statement['student']);
        $this->output
            ->set_content_type('application/pdf')
            ->set_header('Content-Disposition: ' . ($display === 'inline' ? 'inline' : 'attachment') . '; filename="' . $filename . '"')
            ->set_output($pdf);
    }

    /** Email the generated payment statement to the student's primary guardian. */
    public function email_payment_statement($student_id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $academic_year = $this->normalize_payment_school_year($this->input->post('academic_year', true));
        $statement = $this->build_payment_statement((int) $student_id, $academic_year);
        if (!$statement) {
            show_404();
            return;
        }

        if (!$statement['payments']) {
            $this->session->set_flashdata('error', 'There are no payment records to email yet.');
            return redirect('finance/payments?' . http_build_query([
                'search' => $this->input->post('search', true),
                'student_id' => (int) $student_id,
                'academic_year' => $academic_year,
                'grade_level' => $this->input->post('grade_level', true),
                'section' => $this->input->post('section', true)
            ]));
        }

        $guardian = $statement['guardian'];
        if (!$guardian || !filter_var($guardian->email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'No valid primary guardian email is saved for this student.');
            return redirect('finance/payments?' . http_build_query([
                'search' => $this->input->post('search', true),
                'student_id' => (int) $student_id,
                'academic_year' => $academic_year,
                'grade_level' => $this->input->post('grade_level', true),
                'section' => $this->input->post('section', true)
            ]));
        }

        $this->load->library('Payment_statement_pdf');
        $pdf = $this->payment_statement_pdf->generate(
            $statement['student'],
            $statement['payments'],
            $statement['primary_guardian'],
            $this->payment_statement_signer()
        );
        $student_name = trim($statement['student']->first_name . ' ' . $statement['student']->last_name);
        $filename = $this->payment_statement_filename($statement['student']);
        $sent = $this->mail_service->sendWithAttachment(
            $guardian->email,
            'Payment history - ' . $student_name,
            '<p>Dear ' . html_escape(trim($guardian->first_name . ' ' . $guardian->last_name)) . ',</p>'
                . '<p>Attached is the payment history statement for ' . html_escape($student_name) . '.</p>'
                . '<p>Regards,<br>Precious Little Lights Academy</p>',
            $pdf,
            $filename
        );

        if (!$sent['status']) {
            log_message('error', 'Payment statement email failed: ' . ($sent['message'] ?? 'Unknown mail server error.'));
        }

        $this->session->set_flashdata(
            $sent['status'] ? 'success' : 'error',
            $sent['status']
                ? 'Payment history PDF sent to ' . $guardian->email . '.'
                : 'The email could not be sent: ' . ($sent['message'] ?? 'Unknown mail server error.')
        );
        return redirect('finance/payments?' . http_build_query([
            'search' => $this->input->post('search', true),
            'student_id' => (int) $student_id,
            'academic_year' => $academic_year,
            'grade_level' => $this->input->post('grade_level', true),
            'section' => $this->input->post('section', true)
        ]));
    }

    private function build_payment_statement($student_id, $academic_year = null)
    {
        $student = $this->payment_model->get_student_payment_summary($student_id, $academic_year);
        if (!$student) {
            return null;
        }

        $payments = $this->payment_model->get_payment_history_by_student($student_id, $academic_year);
        $payments = array_reverse($payments);

        return [
            'student' => $student,
            'guardian' => $this->payment_model->get_statement_guardian($student_id),
            'primary_guardian' => $this->payment_model->get_primary_guardian($student_id),
            'payments' => $payments
        ];
    }

    private function payment_statement_signer()
    {
        $user = $this->current_user;
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($name === '') {
            $name = trim((string) ($user->username ?? ''));
        }

        $role_id = (int) ($user->role_id ?? $this->session->userdata('role_id'));
        $role = $role_id === 1 ? 'ADMIN' : ($role_id === 2 ? 'PRINCIPAL' : 'AUTHORIZED FINANCE STAFF');

        return [
            'name' => $name !== '' ? $name : 'Authorized school representative',
            'role' => $role
        ];
    }

    private function payment_statement_filename($student)
    {
        $student_name = trim(implode(' ', array_filter([
            $student->first_name ?? '',
            $student->middle_name ?? '',
            $student->last_name ?? '',
            $student->suffix ?? ''
        ])));
        if (function_exists('iconv')) {
            $ascii_name = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $student_name);
            if ($ascii_name !== false) {
                $student_name = $ascii_name;
            }
        }

        $student_name = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $student_name), '-');
        return ($student_name ?: 'Student') . '-' . date('Y-m-d') . '.pdf';
    }

    private function normalize_payment_school_year($requested_year)
    {
        $requested_year = trim((string) $requested_year);
        if ($requested_year === '') {
            return null;
        }

        foreach ($this->fee_configuration_model->get_school_years() as $year) {
            if ((string) $year->school_year === $requested_year) {
                return $requested_year;
            }
        }

        return null;
    }
}
