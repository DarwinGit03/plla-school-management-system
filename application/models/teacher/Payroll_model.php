<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll_model extends CI_Model
{
    public function get_periods()
    {
        return $this->db
            ->select('periods.*, (SELECT COUNT(*) FROM payroll_items AS items WHERE items.payroll_period_id = periods.id) AS employee_count', false)
            ->from('payroll_periods AS periods')
            ->order_by('periods.period_start', 'DESC')
            ->get()
            ->result();
    }

    public function get_deduction_settings($effective_date = null)
    {
        $this->db->from('payroll_deduction_settings');
        if ($effective_date) $this->db->where('effective_from <=', $effective_date);
        return $this->db->order_by('effective_from', 'DESC')->limit(1)->get()->row();
    }

    public function get_deduction_settings_history()
    {
        return $this->db->order_by('effective_from', 'DESC')->get('payroll_deduction_settings')->result();
    }

    public function save_deduction_settings(array $values, $created_by)
    {
        $fields = ['sss_employee_rate', 'philhealth_employee_rate', 'pagibig_high_rate'];
        foreach ($fields as $field) {
            if (!isset($values[$field]) || !is_numeric($values[$field]) || (float) $values[$field] < 0) {
                return ['ok' => false, 'code' => 'invalid_setting'];
            }
        }
        foreach ($fields as $field) {
            if ((float) $values[$field] > 100) {
                return ['ok' => false, 'code' => 'invalid_setting'];
            }
        }
        if ((float) $values['pagibig_high_rate'] < (float) $this->get_deduction_settings()->pagibig_low_rate) {
            return ['ok' => false, 'code' => 'invalid_setting'];
        }
        $latest = $this->get_deduction_settings_history();
        if ($values['effective_from'] < date('Y-m-d') || ($latest && $values['effective_from'] <= $latest[0]->effective_from)) return ['ok' => false, 'code' => 'invalid_effective_date'];
        $base = $latest ? (array) $latest[0] : [];
        unset($base['id']);
        $insert = array_merge($base, [
            'effective_from' => $values['effective_from'],
            'sss_employee_rate' => number_format((float) $values['sss_employee_rate'], 3, '.', ''),
            'sss_mpf_employee_rate' => number_format((float) $values['sss_employee_rate'], 3, '.', ''),
            'philhealth_total_rate' => number_format((float) $values['philhealth_employee_rate'], 3, '.', ''),
            'philhealth_employee_share' => '100.000',
            'pagibig_high_rate' => number_format((float) $values['pagibig_high_rate'], 3, '.', ''),
            'created_by' => (string) $created_by,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert('payroll_deduction_settings', $insert)
            ? ['ok' => true, 'id' => (int) $this->db->insert_id()]
            : ['ok' => false, 'code' => 'save_failed'];
    }

    private function calculate_statutory_deductions($item, $period, $gross_pay)
    {
        $settings = $this->get_deduction_settings($period->period_start);
        if (!$settings) return ['sss_amount' => 0, 'philhealth_amount' => 0, 'pagibig_amount' => 0, 'withholding_tax_amount' => 0];
        if ($item->salary_type_snapshot === 'daily' && empty($item->attendance_confirmed)) {
            return ['sss_amount' => '0.00', 'philhealth_amount' => '0.00', 'pagibig_amount' => '0.00', 'withholding_tax_amount' => '0.00'];
        }

        $monthlyBase = $item->salary_type_snapshot === 'daily'
            ? (float) $item->daily_salary_snapshot * 26
            : (float) $item->monthly_salary_snapshot;
        $factor = $period->frequency === 'weekly' ? 12 / 52 : 0.5;
        $creditStep = (float) $settings->sss_credit_step;
        $msc = $creditStep > 0 ? floor(($monthlyBase + ($creditStep / 2)) / $creditStep) * $creditStep : $monthlyBase;
        $msc = min((float) $settings->sss_max_base, max((float) $settings->sss_min_base, $msc));
        $monthlySss = min($msc, (float) $settings->sss_mpf_threshold) * ((float) $settings->sss_employee_rate / 100)
            + max(0, $msc - (float) $settings->sss_mpf_threshold) * ((float) $settings->sss_mpf_employee_rate / 100);
        $phBase = min((float) $settings->philhealth_max_base, max((float) $settings->philhealth_min_base, $monthlyBase));
        $monthlyPhilhealth = $phBase * ((float) $settings->philhealth_total_rate / 100)
            * ((float) $settings->philhealth_employee_share / 100);
        $pagibigBase = min((float) $settings->pagibig_max_base, $monthlyBase);
        $pagibigRate = $monthlyBase <= (float) $settings->pagibig_low_threshold
            ? (float) $settings->pagibig_low_rate : (float) $settings->pagibig_high_rate;
        $monthlyPagibig = $pagibigBase * ($pagibigRate / 100);
        $sss = round($monthlySss * $factor, 2);
        $philhealth = round($monthlyPhilhealth * $factor, 2);
        $pagibig = round($monthlyPagibig * $factor, 2);

        $taxableCompensation = max(0, (float) $gross_pay - (float) $item->absence_deduction);
        $taxable = max(0, $taxableCompensation - $sss - $philhealth - $pagibig);
        $brackets = $period->frequency === 'weekly'
            ? [[0, 0, 0], [4808, 0, .15], [7692, 432.60, .20], [15385, 1971.20, .25], [38462, 7740.45, .30], [153846, 42355.65, .35]]
            : [[0, 0, 0], [10417, 0, .15], [16667, 937.50, .20], [33333, 4270.70, .25], [83333, 16770.70, .30], [333333, 91770.70, .35]];
        $withholding = 0.0;
        foreach ($brackets as $bracket) {
            if ($taxable < $bracket[0]) break;
            $withholding = $bracket[1] + max(0, $taxable - $bracket[0]) * $bracket[2];
        }
        return [
            'sss_amount' => number_format($sss, 2, '.', ''),
            'philhealth_amount' => number_format($philhealth, 2, '.', ''),
            'pagibig_amount' => number_format($pagibig, 2, '.', ''),
            'withholding_tax_amount' => number_format(max(0, round($withholding, 2)), 2, '.', '')
        ];
    }

    private function save_statutory_snapshot($item, $period, $gross_pay)
    {
        $amounts = $this->calculate_statutory_deductions($item, $period, $gross_pay);
        $existing = $this->db->where('payroll_item_id', (int) $item->id)->get('payroll_tax_deductions')->row();
        $taxTotal = (float) $amounts['sss_amount'] + (float) $amounts['philhealth_amount'] + (float) $amounts['pagibig_amount'] + (float) $amounts['withholding_tax_amount'];
        $manual = (float) $this->db->select_sum('amount', 'total')->where('payroll_item_id', (int) $item->id)->get('payroll_deductions')->row()->total;
        $deductionTotal = $taxTotal + $manual + (float) $item->absence_deduction;
        $record = $amounts + ['payroll_item_id' => (int) $item->id, 'updated_by' => 'system', 'updated_at' => date('Y-m-d H:i:s')];
        if ($existing) {
            $this->db->where('payroll_item_id', (int) $item->id)->update('payroll_tax_deductions', $record);
        } else {
            $record['created_by'] = 'system';
            $record['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('payroll_tax_deductions', $record);
        }
        $this->db->where('id', (int) $item->id)->update('payroll_items', [
            'total_deductions' => number_format($deductionTotal, 2, '.', ''),
            'net_pay' => number_format(max(0, (float) $gross_pay - $deductionTotal), 2, '.', '')
        ]);
    }

    public function get_paid_history($search = '', $limit = null)
    {
        $this->db
            ->select('items.id AS payroll_item_id, items.teacher_id, items.employee_no_snapshot, items.teacher_name_snapshot, items.salary_type_snapshot, items.payable_days, items.paid_leave_days, items.absence_days, items.absence_deduction, items.basic_pay, items.total_deductions, items.net_pay, items.payment_method, items.payment_reference, items.paid_at, periods.period_start, periods.period_end, periods.pay_date, periods.frequency, periods.finalized_at')
            ->from('payroll_items AS items')
            ->join('payroll_periods AS periods', 'periods.id = items.payroll_period_id')
            ->where('periods.status', 'finalized')
            ->where('items.payment_status', 'paid');

        $search = trim((string) $search);
        if ($search !== '') {
            $this->db->group_start()
                ->like('items.teacher_name_snapshot', $search)
                ->or_like('items.employee_no_snapshot', $search)
                ->group_end();
        }

        $this->db
            ->order_by('periods.pay_date', 'DESC')
            ->order_by('items.teacher_name_snapshot', 'ASC');
        if ($limit !== null) {
            $this->db->limit(max(1, (int) $limit));
        }
        return $this->db->get()->result();
    }

    public function get_period($id)
    {
        return $this->db->where('id', (int) $id)->get('payroll_periods')->row();
    }

    public function get_period_items($period_id)
    {
        return $this->db
            ->where('payroll_period_id', (int) $period_id)
            ->order_by('teacher_name_snapshot', 'ASC')
            ->get('payroll_items')
            ->result();
    }

    public function refresh_draft_period_deductions($period_id)
    {
        $this->db->trans_begin();
        $query = $this->db->where('id', (int) $period_id)->get_compiled_select('payroll_periods');
        $period = $this->db->query($query . ' FOR UPDATE')->row();
        if (!$period || $period->status !== 'draft') {
            $this->db->trans_rollback();
            return false;
        }
        foreach ($this->get_period_items($period_id) as $item) {
            $this->save_statutory_snapshot($item, $period, (float) $item->basic_pay);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function get_teacher_history($teacher_id, $finalized_only = false)
    {
        $this->db
            ->select('items.*, periods.period_start, periods.period_end, periods.pay_date, periods.frequency, periods.status AS period_status, ((SELECT COALESCE(SUM(deduction.amount), 0) FROM payroll_deductions AS deduction WHERE deduction.payroll_item_id = items.id) + items.absence_deduction + (SELECT COALESCE(SUM(tax.sss_amount + tax.philhealth_amount + tax.pagibig_amount + tax.withholding_tax_amount), 0) FROM payroll_tax_deductions AS tax WHERE tax.payroll_item_id = items.id)) AS total_deductions', false)
            ->from('payroll_items AS items')
            ->join('payroll_periods AS periods', 'periods.id = items.payroll_period_id')
            ->where('items.teacher_id', (int) $teacher_id);
        if ($finalized_only) {
            $this->db->where('periods.status', 'finalized');
        }
        return $this->db
            ->order_by('periods.period_start', 'DESC')
            ->order_by('periods.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_deductions_for_items(array $item_ids)
    {
        if (!$item_ids) return [];
        $rows = $this->db
            ->where_in('payroll_item_id', array_map('intval', $item_ids))
            ->order_by('description', 'ASC')
            ->get('payroll_deductions')
            ->result();
        $grouped = [];
        foreach ($rows as $row) $grouped[$row->payroll_item_id][] = $row;
        return $grouped;
    }

    public function get_tax_deductions_for_items(array $item_ids)
    {
        if (!$item_ids) return [];
        $rows = $this->db
            ->where_in('payroll_item_id', array_map('intval', $item_ids))
            ->get('payroll_tax_deductions')
            ->result();
        $grouped = [];
        foreach ($rows as $row) $grouped[$row->payroll_item_id] = $row;
        return $grouped;
    }

    public function get_item($id)
    {
        return $this->db
            ->select('items.*, periods.period_start, periods.period_end, periods.pay_date, periods.status AS period_status')
            ->from('payroll_items AS items')
            ->join('payroll_periods AS periods', 'periods.id = items.payroll_period_id')
            ->where('items.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_deduction($id)
    {
        return $this->db
            ->select('deductions.*, items.payroll_period_id')
            ->from('payroll_deductions AS deductions')
            ->join('payroll_items AS items', 'items.id = deductions.payroll_item_id')
            ->where('deductions.id', (int) $id)
            ->get()
            ->row();
    }

    public function create_period(array $data, $created_by)
    {
        $this->db->trans_begin();

        $overlap = $this->db->query(
            'SELECT id FROM payroll_periods WHERE frequency = ? AND period_start <= ? AND period_end >= ? FOR UPDATE',
            [$data['frequency'], $data['period_end'], $data['period_start']]
        )->row();
        if ($overlap) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'period_overlap'];
        }

        $salaryChange = $this->db
            ->from('teacher_salary_history AS salary')
            ->join('teachers', 'teachers.id = salary.teacher_id')
            ->where('teachers.status', 'active')
            ->where('salary.effective_from >', $data['period_start'])
            ->where('salary.effective_from <=', $data['period_end'])
            ->count_all_results();
        if ($salaryChange > 0) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'salary_change_in_period'];
        }

        $teachers = $this->db
            ->select('teachers.id, teachers.employee_no, teachers.first_name, teachers.middle_name, teachers.last_name, teachers.suffix, teachers.position_title, salary.salary_type, salary.pay_frequency, salary.monthly_salary, salary.daily_salary')
            ->from('teachers')
            ->join(
                'teacher_salary_history AS salary',
                'salary.teacher_id = teachers.id AND salary.effective_from <= ' . $this->db->escape($data['period_start']) . ' AND (salary.effective_to IS NULL OR salary.effective_to >= ' . $this->db->escape($data['period_start']) . ')',
                'inner',
                false
            )
            ->where('teachers.status', 'active')
            ->order_by('teachers.id', 'ASC')
            ->get()
            ->result();

        $activeCount = $this->db->where('status', 'active')->count_all_results('teachers');
        if ($activeCount === 0) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'no_active_teachers'];
        }
        $allActiveRates = $this->db
            ->select('teachers.id')
            ->from('teachers')
            ->join(
                'teacher_salary_history AS salary',
                'salary.teacher_id = teachers.id AND salary.effective_from <= ' . $this->db->escape($data['period_start']) . ' AND (salary.effective_to IS NULL OR salary.effective_to >= ' . $this->db->escape($data['period_start']) . ')',
                'inner',
                false
            )
            ->where('teachers.status', 'active')
            ->get()
            ->result();
        if (count($allActiveRates) !== $activeCount) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'missing_salary'];
        }
        if (!$teachers) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'no_teachers_for_schedule'];
        }

        $this->db->insert('payroll_periods', [
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'pay_date' => $data['pay_date'],
            'frequency' => $data['frequency'],
            'status' => 'draft',
            'created_by' => (string) $created_by,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $periodId = (int) $this->db->insert_id();
        if ($periodId < 1) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }

        $items = [];
        foreach ($teachers as $teacher) {
            $salaryType = $teacher->salary_type ?: 'monthly';
            $monthlySalary = $salaryType === 'monthly' ? (float) $teacher->monthly_salary : null;
            $dailySalary = $salaryType === 'daily' ? (float) $teacher->daily_salary : null;
            if ($salaryType !== 'monthly') {
                $basicPay = 0.00;
            } elseif ($data['frequency'] === 'weekly') {
                $basicPay = round(($monthlySalary * 12) / 52, 2);
            } else {
                $basicPay = round($monthlySalary / 2, 2);
            }
            $items[] = [
                'payroll_period_id' => $periodId,
                'teacher_id' => (int) $teacher->id,
                'employee_no_snapshot' => $teacher->employee_no,
                'teacher_name_snapshot' => trim(implode(' ', array_filter([
                    $teacher->first_name,
                    $teacher->middle_name,
                    $teacher->last_name,
                    $teacher->suffix
                ]))),
                'salary_type_snapshot' => $salaryType,
                'position_snapshot' => $teacher->position_title,
                'monthly_salary_snapshot' => $monthlySalary === null ? null : number_format($monthlySalary, 2, '.', ''),
                'daily_salary_snapshot' => $dailySalary === null ? null : number_format($dailySalary, 2, '.', ''),
                'payable_days' => null,
                'basic_pay' => number_format($basicPay, 2, '.', ''),
                'total_deductions' => '0.00',
                'net_pay' => number_format($basicPay, 2, '.', '')
            ];
        }
        $this->db->insert_batch('payroll_items', $items);

        $period = $this->get_period($periodId);
        foreach ($this->get_period_items($periodId) as $item) {
            $this->save_statutory_snapshot($item, $period, (float) $item->basic_pay);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'id' => $periodId, 'item_count' => count($items)];
    }

    public function set_payable_days($item_id, $days, $absence_days = 0, $paid_leave_days = 0)
    {
        $this->db->trans_begin();
        $item = $this->lock_item($item_id);
        if (!$item || $item->status !== 'draft') {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'period_locked'];
        }

        $period = $this->get_period($item->payroll_period_id);
        $periodDays = (int) (new DateTime($period->period_start))->diff(new DateTime($period->period_end))->days + 1;
        $attendanceDays = (float) $days;
        $absenceDays = (float) $absence_days;
        $paidLeaveDays = (float) $paid_leave_days;
        $allDays = $attendanceDays + $absenceDays + $paidLeaveDays;
        if (!is_numeric($days) || !is_numeric($absence_days) || !is_numeric($paid_leave_days)
            || min($attendanceDays, $absenceDays, $paidLeaveDays) < 0 || $allDays > $periodDays
            || abs(($attendanceDays * 2) - round($attendanceDays * 2)) > 0.00001
            || abs(($absenceDays * 2) - round($absenceDays * 2)) > 0.00001
            || abs(($paidLeaveDays * 2) - round($paidLeaveDays * 2)) > 0.00001) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'invalid_days'];
        }

        $basicPay = $item->salary_type_snapshot === 'daily'
            ? round((float) $item->daily_salary_snapshot * ($attendanceDays + $paidLeaveDays), 2)
            : (float) $item->basic_pay;
        $absenceDeduction = $item->salary_type_snapshot === 'monthly'
            ? round(((float) $item->monthly_salary_snapshot / 26) * $absenceDays, 2)
            : 0.00;
        $manualDeductions = (float) $this->db->select_sum('amount', 'total')
            ->where('payroll_item_id', (int) $item_id)->get('payroll_deductions')->row()->total;
        $taxRow = $this->db->where('payroll_item_id', (int) $item_id)->get('payroll_tax_deductions')->row();
        $taxTotal = $taxRow ? (float) $taxRow->sss_amount + (float) $taxRow->philhealth_amount + (float) $taxRow->pagibig_amount + (float) $taxRow->withholding_tax_amount : 0;
        $deductionTotal = $manualDeductions + $absenceDeduction + $taxTotal;
        if ($deductionTotal > $basicPay) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'deduction_exceeds_pay'];
        }

        $this->db->where('id', (int) $item_id)->update('payroll_items', [
            'payable_days' => number_format($attendanceDays, 2, '.', ''),
            'absence_days' => number_format($absenceDays, 2, '.', ''),
            'paid_leave_days' => number_format($paidLeaveDays, 2, '.', ''),
            'absence_deduction' => number_format($absenceDeduction, 2, '.', ''),
            'attendance_confirmed' => 1,
            'basic_pay' => number_format($basicPay, 2, '.', ''),
            'total_deductions' => number_format($deductionTotal, 2, '.', ''),
            'net_pay' => number_format($basicPay - $deductionTotal, 2, '.', '')
        ]);
        $item->absence_deduction = $absenceDeduction;
        $item->attendance_confirmed = 1;
        $this->save_statutory_snapshot($item, $period, $basicPay);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true];
    }

    public function add_deduction($item_id, $category, $description, $amount, $notes, $created_by)
    {
        $this->db->trans_begin();
        $item = $this->lock_item($item_id);
        if (!$item || $item->status !== 'draft') {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'period_locked'];
        }

        $deductionTotal = (float) $this->db
            ->select_sum('amount', 'total')
            ->where('payroll_item_id', (int) $item_id)
            ->get('payroll_deductions')
            ->row()
            ->total;
        $taxRow = $this->db->where('payroll_item_id', (int) $item_id)->get('payroll_tax_deductions')->row();
        $taxTotal = $taxRow ? (float) $taxRow->sss_amount + (float) $taxRow->philhealth_amount + (float) $taxRow->pagibig_amount + (float) $taxRow->withholding_tax_amount : 0;
        if ($deductionTotal + (float) $amount + (float) $item->absence_deduction + $taxTotal > (float) $item->basic_pay) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'deduction_exceeds_pay'];
        }

        $this->db->insert('payroll_deductions', [
            'payroll_item_id' => (int) $item_id,
            'category' => $category,
            'description' => trim($description),
            'amount' => number_format((float) $amount, 2, '.', ''),
            'notes' => $notes === '' ? null : $notes,
            'created_by' => (string) $created_by,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $id = (int) $this->db->insert_id();
        if ($this->db->trans_status() === false || $id < 1) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'id' => $id];
    }

    public function delete_deduction($deduction_id)
    {
        $this->db->trans_begin();
        $deduction = $this->db
            ->select('deductions.id, deductions.payroll_item_id')
            ->from('payroll_deductions AS deductions')
            ->where('deductions.id', (int) $deduction_id)
            ->get()
            ->row();
        if (!$deduction) {
            $this->db->trans_rollback();
            return false;
        }
        $item = $this->lock_item($deduction->payroll_item_id);
        if (!$item || $item->status !== 'draft') {
            $this->db->trans_rollback();
            return false;
        }
        $deleted = $this->db->where('id', (int) $deduction_id)->delete('payroll_deductions');
        if (!$deleted || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function finalize_period($period_id, $finalized_by)
    {
        $this->db->trans_begin();
        $periodQuery = $this->db->where('id', (int) $period_id)->get_compiled_select('payroll_periods');
        $period = $this->db->query($periodQuery . ' FOR UPDATE')->row();
        if (!$period || $period->status !== 'draft') {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'period_locked'];
        }

        $items = $this->db
            ->where('payroll_period_id', (int) $period_id)
            ->get('payroll_items')
            ->result();
        if (!$items) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'empty_period'];
        }

        foreach ($items as $item) {
            if (empty($item->attendance_confirmed)) {
                $this->db->trans_rollback();
                return ['ok' => false, 'code' => 'missing_attendance'];
            }
            $sum = $this->db
                ->select_sum('amount', 'total')
                ->where('payroll_item_id', (int) $item->id)
                ->get('payroll_deductions')
                ->row();
            $tax = $this->db->where('payroll_item_id', (int) $item->id)->get('payroll_tax_deductions')->row();
            $taxTotal = $tax ? (float) $tax->sss_amount + (float) $tax->philhealth_amount + (float) $tax->pagibig_amount + (float) $tax->withholding_tax_amount : 0;
            $deductions = (float) ($sum->total ?? 0) + (float) $item->absence_deduction + $taxTotal;
            if ($deductions > (float) $item->basic_pay) {
                $this->db->trans_rollback();
                return ['ok' => false, 'code' => 'deduction_exceeds_pay'];
            }
            $this->db->where('id', (int) $item->id)->update('payroll_items', [
                'total_deductions' => number_format($deductions, 2, '.', ''),
                'net_pay' => number_format((float) $item->basic_pay - $deductions, 2, '.', '')
            ]);
        }

        $this->db
            ->where('id', (int) $period_id)
            ->update('payroll_periods', [
                'status' => 'finalized',
                'finalized_by' => (string) $finalized_by,
                'finalized_at' => date('Y-m-d H:i:s')
            ]);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'count' => count($items)];
    }

    public function mark_item_paid($item_id, $payment_method, $payment_reference, $paid_by)
    {
        $this->db->trans_begin();
        $item = $this->lock_item($item_id);
        if (!$item || $item->status !== 'finalized') {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'not_finalized'];
        }
        if ($item->payment_status === 'paid') {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'already_paid'];
        }

        $this->db
            ->where('id', (int) $item_id)
            ->where('payment_status', 'unpaid')
            ->set('payment_status', 'paid')
            ->set('payment_method', $payment_method)
            ->set('payment_reference', $payment_reference === '' ? null : $payment_reference)
            ->set('paid_at', 'CURRENT_TIMESTAMP', false)
            ->set('paid_by', (string) $paid_by)
            ->update('payroll_items');

        if ($this->db->affected_rows() !== 1 || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true];
    }

    private function lock_item($id)
    {
        $query = $this->db
            ->select('items.*, periods.status AS status')
            ->from('payroll_items AS items')
            ->join('payroll_periods AS periods', 'periods.id = items.payroll_period_id')
            ->where('items.id', (int) $id)
            ->get_compiled_select();
        return $this->db->query($query . ' FOR UPDATE')->row();
    }
}
