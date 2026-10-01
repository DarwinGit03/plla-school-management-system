<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_payroll extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requirePermission('payroll.self.view');
        $this->load->library('form_validation');
        $this->load->library('password_validator');
        $this->load->model('teacher/teacher_model');
        $this->load->model('teacher/payroll_model');
        $this->load->model('teacher/employee_portal_model');
    }

    public function index()
    {
        $teacher = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        $this->render_page([
            'title' => 'My salary and payslips',
            'page_title' => 'My salary and payslips',
            'page_subtitle' => 'Review your salary rate history and view or download your finalized payslips.',
            'breadcrumb' => ['Dashboard', 'My salary and payslips'],
            'teacher' => $teacher,
            'account' => $this->User_model->getById((int) $this->session->userdata('user_id')),
            'salary_history' => $teacher ? $this->teacher_model->get_salary_history($teacher->id) : [],
            'payroll_items' => $teacher ? $this->payroll_model->get_teacher_history($teacher->id, true) : [],
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function update_profile()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $userId = (int) $this->session->userdata('user_id');
        $account = $this->User_model->getById($userId);
        $teacher = $this->teacher_model->get_by_user_id($userId);
        if (!$account || !$teacher) { show_error('Your employee profile is not linked to this account.', 403); return; }

        $this->form_validation->set_rules('first_name', 'First name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('middle_name', 'Middle name', 'trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|alpha_dash|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[150]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[40]');
        $this->form_validation->set_rules('current_password', 'Current password', 'required|max_length[72]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', trim(strip_tags(validation_errors(' ', ' '))));
            return redirect('my-payroll#edit-account');
        }
        if (!password_verify((string) $this->input->post('current_password'), $account->password)) {
            $this->session->set_flashdata('error', 'Your current password is incorrect.');
            return redirect('my-payroll#edit-account');
        }

        $result = $this->employee_portal_model->update_profile($userId, $teacher->id, [
            'first_name' => $this->input->post('first_name', true),
            'middle_name' => $this->input->post('middle_name', true),
            'last_name' => $this->input->post('last_name', true),
            'username' => $this->input->post('username', true),
            'email' => $this->input->post('email', true),
            'phone' => $this->input->post('phone', true)
        ]);
        if (!$result['ok']) {
            $messages = [
                'duplicate_username' => 'That username is already in use. Choose another one.',
                'duplicate_email' => 'That email address is already in use. Choose another one.',
                'invalid_profile' => 'Your employee profile could not be verified. Please sign in again or contact an administrator.',
                'save_failed' => 'We could not save your account details. Please try again.'
            ];
            $this->session->set_flashdata('error', $messages[$result['code']] ?? 'We could not save your account details.');
            return redirect('my-payroll#edit-account');
        }

        $this->session->set_userdata('email', $this->input->post('email', true));
        $this->audit_log_service->log('account', 'PROFILE_UPDATED', $userId, 'Employee updated their own account details.');
        $this->session->set_flashdata('success', 'Your account details were updated.');
        return redirect('my-payroll#account-settings');
    }

    public function update_password()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $userId = (int) $this->session->userdata('user_id');
        $account = $this->User_model->getById($userId);
        if (!$account || !password_verify((string) $this->input->post('current_password'), $account->password)) {
            $this->session->set_flashdata('error', 'Your current password is incorrect.');
            return redirect('my-payroll#change-password');
        }

        $newPassword = (string) $this->input->post('new_password');
        $confirmPassword = (string) $this->input->post('password_confirm');
        if ($newPassword === '' || $confirmPassword === '') {
            $this->session->set_flashdata('error', 'Enter and confirm your new password.');
            return redirect('my-payroll#change-password');
        }
        if ($newPassword !== $confirmPassword) {
            $this->session->set_flashdata('error', 'The new password and confirmation do not match.');
            return redirect('my-payroll#change-password');
        }
        $passwordErrors = $this->password_validator->validate($newPassword);
        if ($passwordErrors) {
            $this->session->set_flashdata('error', $this->password_validator->error_message($passwordErrors));
            return redirect('my-payroll#change-password');
        }

        $tokenBytes = max(16, (int) $this->config->item('session_token_length'));
        $sessionToken = bin2hex(random_bytes($tokenBytes));
        $saved = $this->User_model->updatePasswordAndSession($userId, password_hash($newPassword, PASSWORD_DEFAULT), $sessionToken);
        if (!$saved) {
            $this->session->set_flashdata('error', 'Your password could not be changed. Please try again.');
            return redirect('my-payroll#change-password');
        }

        $this->session->set_userdata('session_token', $sessionToken);
        $this->audit_log_service->log('account', 'PASSWORD_CHANGED', $userId, 'Employee changed their own password.');
        $this->session->set_flashdata('success', 'Your password was changed. Other active sessions have been signed out.');
        return redirect('my-payroll#account-settings');
    }

    public function payslip($item_id)
    {
        $teacher = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        $item = $this->payroll_model->get_item((int) $item_id);
        if (!$teacher || !$item || (int) $item->employee_id !== (int) $teacher->id || $item->period_status !== 'finalized') {
            show_404();
            return;
        }

        $deductions = $this->payroll_model->get_deductions_for_items([(int) $item->id]);
        $taxDeductions = $this->payroll_model->get_tax_deductions_for_items([(int) $item->id]);
        $this->load->library('teacher_payslip_pdf');
        $pdf = $this->teacher_payslip_pdf->generate($item, $deductions[$item->id] ?? [], $taxDeductions[$item->id] ?? null);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $item->employee_no_snapshot)
            . '_payslip_' . $item->period_start . '.pdf';
        $disposition = $this->input->get('view', true) === '1' ? 'inline' : 'attachment';

        return $this->output
            ->set_content_type('application/pdf')
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"')
            ->set_output($pdf);
    }

    public function email_payslip($item_id)
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $userId = (int) $this->session->userdata('user_id');
        $teacher = $this->teacher_model->get_by_user_id($userId);
        $item = $this->payroll_model->get_item((int) $item_id);
        $account = $this->User_model->getById($userId);
        if (!$teacher || !$item || (int) $item->employee_id !== (int) $teacher->id || $item->period_status !== 'finalized') {
            show_404();
            return;
        }
        if (!$account || !filter_var($account->email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Your account does not have a valid email address. Update your email before sending a payslip.');
            return redirect('my-payroll');
        }

        $deductions = $this->payroll_model->get_deductions_for_items([(int) $item->id]);
        $taxDeductions = $this->payroll_model->get_tax_deductions_for_items([(int) $item->id]);
        $this->load->library('teacher_payslip_pdf');
        $this->load->library('mail_service');
        $pdf = $this->teacher_payslip_pdf->generate($item, $deductions[$item->id] ?? [], $taxDeductions[$item->id] ?? null);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $item->employee_no_snapshot)
            . '_payslip_' . $item->period_start . '.pdf';
        $sent = $this->mail_service->sendWithAttachment(
            $account->email,
            'Your payslip - ' . $item->period_start . ' to ' . $item->period_end,
            '<p>Hello ' . html_escape($item->employee_name_snapshot) . ',</p>'
                . '<p>Your finalized payslip for ' . html_escape($item->period_start . ' to ' . $item->period_end) . ' is attached.</p>'
                . '<p>Regards,<br>Precious Little Lights Academy</p>',
            $pdf,
            $filename
        );

        if (!$sent['status']) {
            $this->session->set_flashdata('error', 'The payslip email could not be sent: ' . ($sent['message'] ?? 'Please try again later.'));
        } else {
            $this->audit_log_service->log('payroll_items', 'PAYSLIP_EMAILED', (int) $item->id, 'Employee emailed a finalized payslip to their own account email.');
            $this->session->set_flashdata('success', 'Payslip sent to ' . $account->email . '.');
        }
        return redirect('my-payroll');
    }

    private function render_page(array $data)
    {
        $this->load->view('dashboard/layouts/master', array_merge($data, [
            'content' => 'payroll/my_history'
        ]));
    }
}
