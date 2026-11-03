<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parents extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('parent/Parent_model');
        $this->load->model('academic/Academic_records_model');
        $this->load->model('finance/Fee_configuration_model');
        $this->load->library('Mail_service');
    }

    public function index()
    {
        if (!$this->require_parent_portal_login()) return;

        $email = $this->parent_email();
        $students = $this->Parent_model->get_students_by_guardian_email($email);
        $this->render_parent_page('parents/dashboard', [
            'title' => 'Parent Portal',
            'students' => $students,
            'parent_name' => $this->parent_name($email)
        ]);
    }

    public function child($studentId = 0)
    {
        if (!$this->require_parent_portal_login()) return;
        $email = $this->parent_email();
        $studentId = (int) $studentId;
        $student = $this->Parent_model->get_linked_student($email, $studentId);
        if (!$student) show_404();

        $enrollments = $this->Parent_model->get_student_enrollments($studentId);
        $requestedEnrollmentId = (int) $this->input->get('enrollment_id', true);
        $selectedEnrollment = null;
        foreach ($enrollments as $enrollment) {
            if ($requestedEnrollmentId > 0 && (int) $enrollment->id === $requestedEnrollmentId) {
                $selectedEnrollment = $enrollment;
                break;
            }
        }
        if ($requestedEnrollmentId > 0 && !$selectedEnrollment) show_404();
        if (!$selectedEnrollment && !empty($enrollments)) {
            foreach ($enrollments as $enrollment) {
                if ($enrollment->status === 'active') { $selectedEnrollment = $enrollment; break; }
            }
            if (!$selectedEnrollment) $selectedEnrollment = $enrollments[0];
        }

        $card = $selectedEnrollment
            ? $this->Academic_records_model->get_report_card((int) $selectedEnrollment->id)
            : null;
        // Verify the report-card lookup resolved to the same child before exposing any records.
        if ($card && (int) $card['enrollment']->student_id !== $studentId) show_404();
        $feeItems = $selectedEnrollment
            ? $this->Parent_model->get_fee_items_for_enrollment($studentId, (int) $selectedEnrollment->id)
            : [];
        $financeTotals = ['assessed' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
        $feeGroups = [];
        foreach ($feeItems as $feeItem) {
            $financeTotals['assessed'] += (float) $feeItem->amount_due;
            $financeTotals['paid'] += (float) $feeItem->amount_paid;
            $financeTotals['balance'] += (float) $feeItem->balance;

            $category = trim((string) $feeItem->fee_category);
            if (!isset($feeGroups[$category])) {
                $feeGroups[$category] = [
                    'category' => $category,
                    'items' => [],
                    'assessed' => 0.0,
                    'paid' => 0.0,
                    'balance' => 0.0
                ];
            }
            $feeGroups[$category]['items'][] = $feeItem;
            $feeGroups[$category]['assessed'] += (float) $feeItem->amount_due;
            $feeGroups[$category]['paid'] += (float) $feeItem->amount_paid;
            $feeGroups[$category]['balance'] += (float) $feeItem->balance;
        }
        $payments = $this->Parent_model->get_payment_history_by_guardian_email(
            $email,
            $studentId,
            $selectedEnrollment ? $selectedEnrollment->academic_year : null
        );

        $this->render_parent_page('parents/child_records', [
            'title' => 'Student records',
            'student' => $student,
            'enrollments' => $enrollments,
            'selected_enrollment' => $selectedEnrollment,
            'card' => $card,
            'grade_label' => $selectedEnrollment ? $this->Fee_configuration_model->grade_label($selectedEnrollment->grade_level) : '',
            'fee_items' => $feeItems,
            'fee_groups' => array_values($feeGroups),
            'finance_totals' => $financeTotals,
            'payments' => $payments
        ]);
    }

    public function login()
    {
        if ($this->session->userdata('parent_portal_authenticated') === true) return redirect('parent-portal');
        if ($this->input->get('change', true)) $this->session->unset_userdata('parent_portal_pending_email');
        $this->load->view('parents/login', [
            'title' => 'Parent Portal sign in',
            'pending_email' => (string) $this->session->userdata('parent_portal_pending_email'),
            'message' => $this->session->flashdata('parent_portal_message'),
            'message_type' => $this->session->flashdata('parent_portal_message_type') ?: 'info'
        ]);
    }

    public function request_code()
    {
        if ($this->input->method(true) !== 'POST') show_404();
        $email = strtolower(trim((string) $this->input->post('email', true)));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->portal_login_message('Enter a valid email address.', 'danger');
        }

        // Keep the response identical for known and unknown addresses to prevent account discovery.
        if (!$this->Parent_model->has_guardian_email($email)) {
            $this->session->set_flashdata('parent_portal_message', 'If this email is linked to a student, a sign-in code will be sent.');
            $this->session->set_flashdata('parent_portal_message_type', 'info');
            return redirect('parent-portal/login');
        }

        $existing = $this->Parent_model->get_parent_login_code($email);
        if ($existing && strtotime($existing->sent_at) > time() - 60) {
            return $this->portal_login_message('A code was recently sent. Please wait one minute before requesting another.', 'warning', $email);
        }

        $code = (string) random_int(100000, 999999);
        if (!$this->Parent_model->save_parent_login_code($email, password_hash($code, PASSWORD_DEFAULT), date('Y-m-d H:i:s', time() + 300))) {
            log_message('error', 'Could not save a parent portal sign-in code.');
            return $this->portal_login_message('Unable to start sign-in right now. Please try again later.', 'danger', $email);
        }

        $sent = $this->mail_service->sendParentLoginOTP($email, $code);
        if (empty($sent['status'])) {
            $this->Parent_model->delete_parent_login_code($email);
            return $this->portal_login_message('We could not send the code. Please contact the school office.', 'danger', $email);
        }

        $this->session->set_userdata('parent_portal_pending_email', $email);
        $this->session->set_flashdata('parent_portal_message', 'A sign-in code was sent if this email is linked to a student. It expires in 5 minutes.');
        $this->session->set_flashdata('parent_portal_message_type', 'success');
        return redirect('parent-portal/login');
    }

    public function verify_code()
    {
        if ($this->input->method(true) !== 'POST') show_404();
        $email = strtolower(trim((string) $this->session->userdata('parent_portal_pending_email')));
        $code = trim((string) $this->input->post('code', true));
        if ($email === '' || !preg_match('/^[0-9]{6}$/', $code)) {
            return $this->portal_login_message('Enter the six-digit code from your email.', 'danger', $email);
        }

        $record = $this->Parent_model->get_parent_login_code($email);
        if (!$record || strtotime($record->expires_at) < time() || (int) $record->attempts >= 5) {
            $this->Parent_model->delete_parent_login_code($email);
            $this->session->unset_userdata('parent_portal_pending_email');
            return $this->portal_login_message('This code expired or is no longer valid. Request a new code.', 'warning');
        }

        if (!password_verify($code, $record->code_hash)) {
            $this->Parent_model->increment_parent_login_attempts($email);
            return $this->portal_login_message('That code is not correct. Please check it and try again.', 'danger', $email);
        }

        if (!$this->Parent_model->has_guardian_email($email)) {
            $this->Parent_model->delete_parent_login_code($email);
            $this->session->unset_userdata('parent_portal_pending_email');
            return $this->portal_login_message('This parent account is not linked to a student. Contact the school office.', 'danger');
        }

        $this->session->sess_regenerate(true);
        $this->session->set_userdata([
            'parent_portal_authenticated' => true,
            'parent_portal_email' => $email,
            'parent_portal_last_activity' => time()
        ]);
        $this->session->unset_userdata('parent_portal_pending_email');
        $this->Parent_model->delete_parent_login_code($email);
        $this->audit_log_service->log('Parent portal', 'PARENT_LOGIN', null, 'Parent signed in using email verification.');
        return redirect('parent-portal');
    }

    public function payment_history()
    {
        if (!$this->require_parent_portal_login()) return;
        $email = $this->parent_email();
        $students = $this->Parent_model->get_students_by_guardian_email($email);
        $selectedStudentId = (int) $this->input->get('student_id', true);
        if ($selectedStudentId > 0 && !$this->Parent_model->get_linked_student($email, $selectedStudentId)) show_404();
        $payments = $this->Parent_model->get_payment_history_by_guardian_email($email, $selectedStudentId ?: null);
        $this->render_parent_page('parents/payment_history', [
            'title' => 'Payment history',
            'payments' => $payments,
            'students' => $students,
            'selected_student_id' => $selectedStudentId
        ]);
    }

    public function logout()
    {
        if ($this->input->method(true) !== 'POST') show_404();
        $email = $this->parent_email();
        if ($email !== '') {
            $this->audit_log_service->log('Parent portal', 'PARENT_LOGOUT', null, 'Parent signed out.');
        }
        $this->session->unset_userdata([
            'parent_portal_authenticated', 'parent_portal_email',
            'parent_portal_last_activity', 'parent_portal_pending_email'
        ]);
        $this->session->sess_regenerate(true);
        return redirect('parent-portal/login');
    }

    private function require_parent_portal_login()
    {
        if ($this->session->userdata('logged_in') === true && $this->can('parent.payments.view')) {
            return true;
        }
        $email = $this->parent_email();
        $lastActivity = (int) $this->session->userdata('parent_portal_last_activity');
        if ($email === '' || !$lastActivity || time() - $lastActivity > 1800) {
            $this->session->unset_userdata(['parent_portal_authenticated', 'parent_portal_email', 'parent_portal_last_activity']);
            redirect('parent-portal/login');
            return false;
        }
        if (!$this->Parent_model->has_guardian_email($email)) {
            $this->session->unset_userdata(['parent_portal_authenticated', 'parent_portal_email', 'parent_portal_last_activity']);
            show_error('This email is no longer linked to a student.', 403, 'Access denied');
            return false;
        }
        $this->session->set_userdata('parent_portal_last_activity', time());
        return true;
    }

    private function parent_email()
    {
        if ($this->session->userdata('parent_portal_authenticated') === true) {
            return strtolower(trim((string) $this->session->userdata('parent_portal_email')));
        }

        // Preserve access for existing parent-role accounts authenticated through staff login.
        if ($this->session->userdata('logged_in') === true && $this->can('parent.payments.view')) {
            return strtolower(trim((string) $this->session->userdata('email')));
        }
        return '';
    }

    private function parent_name($email)
    {
        $guardian = $this->Parent_model->get_guardian_identity_by_email($email);
        if (!$guardian) return 'Parent';
        return trim(implode(' ', array_filter([
            $guardian->first_name ?? '', $guardian->middle_name ?? '', $guardian->last_name ?? ''
        ], function ($part) { return trim((string) $part) !== ''; })));
    }

    private function portal_login_message($message, $type, $email = '')
    {
        if ($email !== '') $this->session->set_userdata('parent_portal_pending_email', $email);
        $this->session->set_flashdata('parent_portal_message', $message);
        $this->session->set_flashdata('parent_portal_message_type', $type);
        return redirect('parent-portal/login');
    }

    private function render_parent_page($content, array $data)
    {
        $this->load->view('parents/layout', array_merge($data, [
            'content' => $content,
            'parent_name' => $this->parent_name($this->parent_email())
        ]));
    }
}
