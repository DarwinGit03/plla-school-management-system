<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole([1, 2, 4]);
        $this->load->library('form_validation');
        $this->load->model('teacher/payroll_model');
        $this->load->model('teacher/teacher_model');
    }

    public function index()
    {
        $search = trim((string) $this->input->get('q', true));

        $this->render('payroll/index', [
            'title' => 'Teacher Payroll',
            'page_title' => 'Teacher Payroll',
            'page_subtitle' => 'Prepare payroll runs, download period summaries, and review paid history.',
            'breadcrumb' => ['Finance', 'Teacher Payroll'],
            'periods' => $this->payroll_model->get_periods(),



            'paid_history' => $this->payroll_model->get_paid_history($search),
            'search' => $search,
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error'),
            'payroll_form' => $this->session->flashdata('payroll_form') ?: []
        ]);
    }

    public function deduction_settings()
    {
        $this->requireRole([1, 2]);
        $current = $this->payroll_model->get_deduction_settings();
        if (!$current) show_error('Payroll deduction settings are missing. Apply the payroll deduction settings migration.', 500);
        $this->render('payroll/deduction_settings', [
            'title' => 'Payroll deduction settings',
            'page_title' => 'Payroll deduction settings',
            'page_subtitle' => 'Set organization-wide contribution rules once. Payroll runs use the effective settings for their coverage date.',
            'breadcrumb' => ['Finance', 'Teacher Payroll', 'Deduction Settings'],
            'current_settings' => $current,
            'settings_history' => $this->payroll_model->get_deduction_settings_history(),
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function save_deduction_settings()
    {
        $this->requireRole([1, 2]);
        if ($this->input->method() !== 'post') { show_404(); return; }
        $date = trim((string) $this->input->post('effective_from', true));
        $this->form_validation->set_rules('effective_from', 'Effective from', 'trim|required|callback_valid_date');
        $fields = [
            'sss_employee_rate' => 'SSS employee share',
            'philhealth_employee_rate' => 'PhilHealth employee share',
            'pagibig_high_rate' => 'Pag-IBIG employee share'
        ];
        foreach ($fields as $field => $label) $this->form_validation->set_rules($field, $label, 'trim|required|numeric|greater_than_equal_to[0]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', strip_tags(validation_errors(' ', ' ')));
            return redirect('payroll/settings');
        }
        $values = ['effective_from' => $date];
        foreach ($fields as $field => $label) $values[$field] = $this->input->post($field, true);
        $result = $this->payroll_model->save_deduction_settings($values, (string) $this->session->userdata('employee_no'));
        if (!$result['ok']) {
            $message = $result['code'] === 'invalid_effective_date'
                ? 'A new policy must take effect after the latest saved policy.'
                : ($result['code'] === 'invalid_setting'
                    ? 'Enter valid contribution rates from 0 to 100%. Pag-IBIG rate must be at least 1%.'
                    : 'Payroll deduction settings could not be saved.');
            $this->session->set_flashdata('error', $message);
        } else {
            $this->audit_log_service->log('payroll_deduction_settings', 'PAYROLL_DEDUCTION_POLICY_CREATED', $result['id'], 'Effective-dated organization payroll deduction policy created.', ['effective_from' => $date]);
            $this->session->set_flashdata('success', 'Payroll deduction rules saved. New payroll runs will use this policy from its effective date.');
        }
        return redirect('payroll/settings');
    }

    public function teacher_history($teacher_id)
    {
        $teacher = $this->teacher_model->get_by_id($teacher_id);
        if (!$teacher) {
            show_404();
            return;
        }
        $name = trim(implode(' ', array_filter([
            $teacher->first_name,
            $teacher->middle_name,
            $teacher->last_name,
            $teacher->suffix
        ])));
        $this->render('payroll/teacher_history', [
            'title' => 'Employee Payroll',
            'page_title' => 'Employee Payroll',
            'page_subtitle' => 'Payroll periods, deductions, and payments for ' . $name . '.',
            'breadcrumb' => ['Faculty', 'Payroll', 'Employee Payroll'],
            'teacher' => $teacher,
            'payroll_items' => $this->payroll_model->get_teacher_history($teacher->id)
        ]);
    }

    public function create_period()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $this->form_validation->set_rules('period_start', 'Period start', 'trim|required|callback_valid_date');
        $this->form_validation->set_rules('period_end', 'Period end', 'trim|required|callback_valid_date');
        $this->form_validation->set_rules('pay_date', 'Pay date', 'trim|required|callback_valid_date');
        $this->form_validation->set_rules('frequency', 'Payroll frequency', 'trim|required|in_list[weekly,semi_monthly]');
        if (!$this->form_validation->run()) {
            $message = trim(strip_tags(validation_errors(' ', ' ')));
            $this->flash_period_error($message ?: 'Enter a valid pay schedule, salary period start and end, and pay date.');
            return redirect('payroll#preparePayrollModal');
        }

        $data = [
            'period_start' => trim((string) $this->input->post('period_start', true)),
            'period_end' => trim((string) $this->input->post('period_end', true)),
            'pay_date' => trim((string) $this->input->post('pay_date', true)),
            'frequency' => trim((string) $this->input->post('frequency', true))
        ];
        if (!$this->valid_period_shape($data)) {
            $this->flash_period_error('Weekly periods must cover 7 days. For 15th and month-end, use the 1st-15th or 16th-last day of the month. Set the pay date on or after the period end (within 7 days for weekly pay).', $data);
            return redirect('payroll#preparePayrollModal');
        }

        $result = $this->payroll_model->create_period(
            $data,
            (string) $this->session->userdata('employee_no')
        );
        if (!$result['ok']) {
            $messages = [
                'period_overlap' => 'This period overlaps an existing payroll period.',
                'salary_change_in_period' => 'A salary rate changes during this period. Create a period that starts on or after the new rate takes effect.',
                'missing_salary' => 'Every active teacher needs a salary rate that covers the start of this period before payroll can be created.',
                'no_active_teachers' => 'There are no active teacher profiles to include in payroll.',
                'no_teachers_for_schedule' => 'No active teachers are configured for this pay schedule.',
                'save_failed' => 'The payroll period could not be created.'
            ];
            $this->flash_period_error($messages[$result['code']] ?? 'The payroll period could not be created. Please check the payroll database setup.', $data);
            return redirect('payroll#preparePayrollModal');
        }

        $this->audit_log_service->log(
            'payroll_periods',
            'PAYROLL_PERIOD_CREATED',
            $result['id'],
            'Draft teacher payroll period created.',
            $data + ['item_count' => $result['item_count']]
        );
        $this->session->set_flashdata('success', 'Draft payroll created for ' . $result['item_count'] . ' teacher(s).');
        return redirect('payroll/period/' . $result['id']);
    }

    public function period($id)
    {
        $period = $this->payroll_model->get_period($id);
        if (!$period) {
            show_404();
            return;
        }

        if ($period->status === 'draft') {
            $this->payroll_model->refresh_draft_period_deductions($period->id);
        }

        $items = $this->payroll_model->get_period_items($period->id);
        $itemIds = array_map(function ($item) { return (int) $item->id; }, $items);
        $this->render('payroll/period', [
            'title' => 'Payroll Period',
            'page_title' => 'Payroll Period',
            'page_subtitle' => 'Confirm attendance and absences for each employee, then review deductions for this salary period.',
            'breadcrumb' => ['Faculty', 'Payroll', 'Payroll Period'],
            'period' => $period,
            'items' => $items,
            'deductions' => $this->payroll_model->get_deductions_for_items($itemIds),
            'tax_deductions' => $this->payroll_model->get_tax_deductions_for_items($itemIds),
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function download_summary($id)
    {
        $period = $this->payroll_model->get_period($id);
        if (!$period) {
            show_404();
            return;
        }

        $items = $this->payroll_model->get_period_items($period->id);
        $itemIds = array_map(function ($item) { return (int) $item->id; }, $items);
        $deductions = $this->payroll_model->get_deductions_for_items($itemIds);
        $taxDeductions = $this->payroll_model->get_tax_deductions_for_items($itemIds);
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['Salary period', $period->period_start . ' to ' . $period->period_end]);
        fputcsv($stream, ['Pay date', $period->pay_date]);
        fputcsv($stream, ['Schedule', $period->frequency === 'weekly' ? 'Weekly' : '15th and month-end']);
        fputcsv($stream, ['Run status', ucfirst($period->status)]);
        fputcsv($stream, []);
        fputcsv($stream, ['Employee no.', 'Employee', 'Position', 'Salary basis', 'Rate (PHP)', 'Days attended', 'Paid leave days', 'Unpaid absence days', 'Absence deduction (PHP)', 'Gross pay (PHP)', 'Deduction details', 'Other deductions (PHP)', 'SSS employee share (PHP)', 'PhilHealth employee share (PHP)', 'Pag-IBIG employee share (PHP)', 'Withholding tax (PHP)', 'Total deductions (PHP)', 'Net pay (PHP)', 'Payment status', 'Payment method', 'Reference', 'Paid at']);

        $grossTotal = 0.0;
        $deductionTotal = 0.0;
        $netTotal = 0.0;
        foreach ($items as $item) {
            $grossTotal += (float) $item->basic_pay;
            $manualDeductions = array_sum(array_map(function ($deduction) { return (float) $deduction->amount; }, $deductions[$item->id] ?? []));
            $tax = $taxDeductions[$item->id] ?? null;
            $sss = (float) ($tax->sss_amount ?? 0);
            $philhealth = (float) ($tax->philhealth_amount ?? 0);
            $pagibig = (float) ($tax->pagibig_amount ?? 0);
            $withholding = (float) ($tax->withholding_tax_amount ?? 0);
            $itemTotalDeductions = $manualDeductions + (float) $item->absence_deduction + $sss + $philhealth + $pagibig + $withholding;
            $deductionTotal += $itemTotalDeductions;
            $netTotal += (float) $item->basic_pay - $itemTotalDeductions;
            $salaryType = $item->salary_type_snapshot;
            $rate = $salaryType === 'daily' ? $item->daily_salary_snapshot : $item->monthly_salary_snapshot;
            $itemDeductions = [];
            foreach ($deductions[$item->id] ?? [] as $deduction) {
                $itemDeductions[] = ucwords(str_replace('_', ' ', $deduction->category)) . ' - ' . $deduction->description . ': PHP ' . number_format((float) $deduction->amount, 2);
            }
            $paymentStatus = $period->status !== 'finalized'
                ? 'Draft'
                : (($item->payment_status ?? 'unpaid') === 'paid' ? 'Paid' : 'Awaiting payment');

            fputcsv($stream, [
                $this->csv_safe($item->employee_no_snapshot),
                $this->csv_safe($item->teacher_name_snapshot),
                $this->csv_safe($item->position_snapshot),
                $salaryType === 'daily' ? 'Daily' : 'Monthly',
                $rate,
                $item->payable_days,
                $item->paid_leave_days,
                $item->absence_days,
                $item->absence_deduction,
                $item->basic_pay,
                implode('; ', $itemDeductions),
                number_format($manualDeductions, 2, '.', ''),
                number_format($sss, 2, '.', ''),
                number_format($philhealth, 2, '.', ''),
                number_format($pagibig, 2, '.', ''),
                number_format($withholding, 2, '.', ''),
                number_format($itemTotalDeductions, 2, '.', ''),
                number_format((float) $item->basic_pay - $itemTotalDeductions, 2, '.', ''),
                $paymentStatus,
                $item->payment_method,
                $this->csv_safe($item->payment_reference),
                $item->paid_at
            ]);
        }

        fputcsv($stream, []);
        fputcsv($stream, ['TOTAL', '', '', '', '', '', '', '', '', number_format($grossTotal, 2, '.', ''), '', '', '', '', '', '', number_format($deductionTotal, 2, '.', ''), number_format($netTotal, 2, '.', '')]);
        rewind($stream);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($stream);
        fclose($stream);

        $filename = 'payroll-summary-' . $period->period_start . '-to-' . $period->period_end . '.csv';
        return $this->output
            ->set_content_type('text/csv', 'UTF-8')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_output($csv);
    }
    public function add_deduction()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $itemId = (int) $this->input->post('payroll_item_id', true);
        $item = $this->payroll_model->get_item($itemId);
        if (!$item) {
            show_404();
            return;
        }

        $this->form_validation->set_rules('description', 'Deduction description', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('amount', 'Deduction amount', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('notes', 'Notes', 'trim|max_length[500]');
        $this->form_validation->set_rules('category', 'Deduction type', 'trim|required|in_list[other]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', strip_tags(validation_errors(' ', ' ')));
            return redirect('payroll/period/' . $item->payroll_period_id . '#payroll-item-' . $itemId);
        }

        $result = $this->payroll_model->add_deduction(
            $itemId,
            $this->input->post('category', true),
            $this->input->post('description', true),
            $this->input->post('amount', true),
            trim((string) $this->input->post('notes', true)),
            (string) $this->session->userdata('employee_no')
        );
        if (!$result['ok']) {
            $message = $result['code'] === 'deduction_exceeds_pay'
                ? 'Total deductions cannot exceed this teacher’s basic pay.'
                : 'Deductions can only be changed while the payroll is a draft.';
            $this->session->set_flashdata('error', $message);
        } else {
            $this->audit_log_service->log(
                'payroll_deductions',
                'PAYROLL_DEDUCTION_ADDED',
                $result['id'],
                'Manual payroll deduction added.',
                ['payroll_item_id' => $itemId, 'category' => $this->input->post('category', true), 'description' => $this->input->post('description', true), 'amount' => $this->input->post('amount', true)]
            );
            $this->session->set_flashdata('success', 'Deduction added.');
        }
        return redirect('payroll/period/' . $item->payroll_period_id . '#payroll-item-' . $itemId);
    }

    public function set_payable_days()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        $itemId = (int) $this->input->post('payroll_item_id', true);
        $item = $this->payroll_model->get_item($itemId);
        if (!$item) {
            show_404();
            return;
        }
        $this->form_validation->set_rules('payable_days', 'Days attended', 'trim|required|numeric');
        $this->form_validation->set_rules('paid_leave_days', 'Paid leave days', 'trim|required|numeric');
        $this->form_validation->set_rules('absence_days', 'Unpaid absence days', 'trim|required|numeric');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', strip_tags(validation_errors(' ', ' ')));
            return redirect('payroll/period/' . $item->payroll_period_id . '#payroll-item-' . $itemId);
        }
        $result = $this->payroll_model->set_payable_days(
            $itemId,
            $this->input->post('payable_days', true),
            $this->input->post('absence_days', true),
            $this->input->post('paid_leave_days', true)
        );
        if (!$result['ok']) {
            $message = $result['code'] === 'invalid_days'
                ? 'Attendance and absence days must be valid half-day amounts within the salary period.'
                : ($result['code'] === 'deduction_exceeds_pay'
                    ? 'Paid days cannot reduce basic pay below the current deductions.'
                    : 'Paid days can only be updated while the payroll is a draft.');
            $this->session->set_flashdata('error', $message);
        } else {
            $this->session->set_flashdata('success', 'Attendance, paid leave, and salary updated.');
        }
        return redirect('payroll/period/' . $item->payroll_period_id . '#payroll-item-' . $itemId);
    }

    public function delete_deduction()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        $id = (int) $this->input->post('id', true);
        $deduction = $this->payroll_model->get_deduction($id);
        if (!$deduction) {
            show_404();
            return;
        }
        $ok = $this->payroll_model->delete_deduction($id);
        if ($ok) {
            $this->audit_log_service->log(
                'payroll_deductions',
                'PAYROLL_DEDUCTION_REMOVED',
                $id,
                'Manual payroll deduction removed from a draft.',
                ['payroll_item_id' => (int) $deduction->payroll_item_id]
            );
        }
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Deduction removed.' : 'Only draft payroll deductions can be removed.');
        return redirect('payroll/period/' . $deduction->payroll_period_id . '#payroll-item-' . $deduction->payroll_item_id);
    }

    public function finalize()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        $id = (int) $this->input->post('period_id', true);
        $period = $this->payroll_model->get_period($id);
        if (!$period) {
            show_404();
            return;
        }
        $result = $this->payroll_model->finalize_period(
            $id,
            (string) $this->session->userdata('employee_no')
        );
        if (!$result['ok']) {
            $messages = [
                'period_locked' => 'This payroll is already finalized.',
                'empty_period' => 'A payroll without teacher records cannot be finalized.',
                'missing_attendance' => 'Save attendance and absence days for every employee before finalizing.',
                'deduction_exceeds_pay' => 'Correct deductions that exceed a teacher’s basic pay before finalizing.',
                'save_failed' => 'Payroll finalization failed. No changes were kept.'
            ];
            $this->session->set_flashdata('error', $messages[$result['code']] ?? 'Payroll finalization failed.');
        } else {
            $this->audit_log_service->log(
                'payroll_periods',
                'PAYROLL_PERIOD_FINALIZED',
                $id,
                'Teacher payroll period finalized.',
                ['teacher_count' => $result['count']]
            );
            $this->session->set_flashdata('success', 'Payroll finalized. Payslips are ready to download.');
        }
        return redirect('payroll/period/' . $id);
    }

    public function mark_paid()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        $itemId = (int) $this->input->post('payroll_item_id', true);
        $item = $this->payroll_model->get_item($itemId);
        if (!$item) {
            show_404();
            return;
        }

        $this->form_validation->set_rules('payment_method', 'Payment method', 'trim|required|in_list[cash,bank_transfer,check,other]');
        $this->form_validation->set_rules('payment_reference', 'Payment reference', 'trim|max_length[100]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', strip_tags(validation_errors(' ', ' ')));
            return redirect('payroll/period/' . $item->payroll_period_id);
        }

        $method = trim((string) $this->input->post('payment_method', true));
        $reference = trim((string) $this->input->post('payment_reference', true));
        $result = $this->payroll_model->mark_item_paid(
            $itemId,
            $method,
            $reference,
            (string) $this->session->userdata('employee_no')
        );
        if (!$result['ok']) {
            $messages = [
                'not_finalized' => 'Finalize the payroll before recording payment.',
                'already_paid' => 'This employee payment is already recorded as paid.',
                'save_failed' => 'The payment record could not be saved.'
            ];
            $this->session->set_flashdata('error', $messages[$result['code']] ?? 'The payment could not be recorded.');
        } else {
            $this->audit_log_service->log(
                'payroll_items',
                'TEACHER_PAYROLL_MARKED_PAID',
                $itemId,
                'Finalized teacher payroll payment recorded as paid.',
                ['payment_method' => $method, 'payment_reference' => $reference]
            );
            $this->session->set_flashdata('success', 'Employee payment recorded.');
        }
        return redirect('payroll/period/' . $item->payroll_period_id);
    }
    public function payslip($item_id)
    {
        $item = $this->payroll_model->get_item($item_id);
        if (!$item || $item->period_status !== 'finalized') {
            show_404();
            return;
        }

        $deductions = $this->payroll_model->get_deductions_for_items([(int) $item->id]);
        $taxDeductions = $this->payroll_model->get_tax_deductions_for_items([(int) $item->id]);
        $this->load->library('teacher_payslip_pdf');
        $pdf = $this->teacher_payslip_pdf->generate($item, $deductions[$item->id] ?? [], $taxDeductions[$item->id] ?? null);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $item->employee_no_snapshot)
            . '_payslip_' . $item->period_start . '.pdf';

        return $this->output
            ->set_content_type('application/pdf')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_output($pdf);
    }

    public function valid_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        $valid = $date && $date->format('Y-m-d') === $value;
        if (!$valid) {
            $this->form_validation->set_message('valid_date', 'Enter a valid date.');
        }
        return $valid;
    }

    private function flash_period_error($message, array $data = [])
    {
        $message = trim((string) $message);
        $this->session->set_flashdata('error', $message !== '' ? $message : 'Payroll could not be prepared. Check the dates and try again.');
        $this->session->set_flashdata('payroll_form', [
            'frequency' => $data['frequency'] ?? trim((string) $this->input->post('frequency', true)),
            'period_start' => $data['period_start'] ?? trim((string) $this->input->post('period_start', true)),
            'period_end' => $data['period_end'] ?? trim((string) $this->input->post('period_end', true)),
            'pay_date' => $data['pay_date'] ?? trim((string) $this->input->post('pay_date', true))
        ]);
    }
    private function valid_period_shape(array $data)
    {
        if ($data['period_start'] > $data['period_end'] || $data['pay_date'] < $data['period_end']) return false;
        $start = new DateTime($data['period_start']);
        $end = new DateTime($data['period_end']);
        if ($data['frequency'] === 'weekly') {
            $latestPayDate = (clone $end)->modify('+7 days')->format('Y-m-d');
            return $start->diff($end)->days === 6 && $data['pay_date'] <= $latestPayDate;
        }
        if ($start->format('Y-m') !== $end->format('Y-m')) return false;

        $monthEnd = $start->format('Y-m-t');
        return ($start->format('d') === '01' && $end->format('d') === '15')
            ? $data['pay_date'] === $data['period_end']
            : ($start->format('d') === '16' && $data['period_end'] === $monthEnd && $data['pay_date'] === $data['period_end']);
    }

    private function csv_safe($value)
    {
        $value = (string) $value;
        return preg_match('/^[\s\x00-\x1F]*[=+@-]/', $value) ? "'" . $value : $value;
    }
    private function render($content, array $data)
    {
        $this->load->view('dashboard/layouts/master', array_merge($data, ['content' => $content]));
    }
}
