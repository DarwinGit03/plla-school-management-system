<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Data access for fee balances and posted payments. */
class Payment_model extends CI_Model
{
    protected $payments_table = 'student_fee_payments';
    protected $assessment_table = 'student_enrollment_fees';

    /** Return school years that have an assessed fee schedule. */
    public function get_payment_school_years()
    {
        $this->db->reset_query();

        return $this->db
            ->distinct()
            ->select('enrollments.academic_year')
            ->from('student_enrollments AS enrollments')
            ->join(
                $this->assessment_table . ' AS assessed_fees',
                'assessed_fees.enrollment_id = enrollments.id',
                'inner'
            )
            ->where('enrollments.academic_year IS NOT NULL', null, false)
            ->where('enrollments.academic_year !=', '')
            ->order_by('enrollments.academic_year', 'DESC')
            ->get()
            ->result();
    }

    /** Find enrolled students with fee assessments for finance payment lookup. */
    public function search_students_for_payments($search, $academic_year = null)
    {
        $this->db->reset_query();

        $this->db
            ->select("
                students.id AS student_id,
                students.student_no,
                students.lrn,
                students.first_name,
                students.middle_name,
                students.last_name,
                students.suffix,
                enrollments.id AS enrollment_id,
                enrollments.academic_year,
                enrollments.grade_level,
                COUNT(DISTINCT CASE
                    WHEN assessed_fees.amount > COALESCE(posted_payments.amount_paid, 0)
                    THEN assessed_fees.id
                END) AS outstanding_items,
                SUM(CASE
                    WHEN assessed_fees.amount > COALESCE(posted_payments.amount_paid, 0)
                    THEN assessed_fees.amount - COALESCE(posted_payments.amount_paid, 0)
                    ELSE 0
                END) AS outstanding_balance
            ", false)
            ->from('students')
            ->join(
                'student_enrollments AS enrollments',
                'enrollments.student_id = students.id',
                'inner'
            )
            ->join(
                $this->assessment_table . ' AS assessed_fees',
                'assessed_fees.enrollment_id = enrollments.id',
                'inner'
            )
            ->join(
                '(SELECT enrollment_fee_id, SUM(amount_paid) AS amount_paid
                  FROM ' . $this->payments_table . '
                  WHERE status = "posted"
                  GROUP BY enrollment_fee_id) AS posted_payments',
                'posted_payments.enrollment_fee_id = assessed_fees.id',
                'left',
                false
            )
            ->where('students.deleted_at IS NULL', null, false)
            ->group_start()
            ->like('students.student_no', trim($search))
            ->or_like('students.lrn', trim($search))
            ->or_like('students.first_name', trim($search))
            ->or_like('students.middle_name', trim($search))
            ->or_like('students.last_name', trim($search))
            ->or_like('students.suffix', trim($search))
            ->or_like(
                "CONCAT_WS(' ', students.first_name, NULLIF(students.middle_name, ''), students.last_name, NULLIF(students.suffix, ''))",
                trim($search),
                'both',
                false
            )
            ->group_end();

        if ($academic_year !== null && $academic_year !== '') {
            $this->db->where('enrollments.academic_year', $academic_year);
        }

        return $this->db
            ->group_by(['students.id', 'enrollments.id'])
            ->order_by('enrollments.academic_year', 'DESC')
            ->order_by('students.last_name', 'ASC')
            ->order_by('students.first_name', 'ASC')
            ->get()
            ->result();
    }

    /** Get one student summary so the selected record can be confirmed. */
    public function get_student_payment_summary($student_id, $academic_year = null)
    {
        $this->db->reset_query();

        $this->db
            ->select("
                students.id AS student_id,
                students.student_no,
                students.lrn,
                students.first_name,
                students.middle_name,
                students.last_name,
                students.suffix,
                GROUP_CONCAT(DISTINCT CONCAT(enrollments.academic_year, ' - Grade ', enrollments.grade_level) ORDER BY enrollments.academic_year DESC SEPARATOR ', ') AS enrollments,
                SUM(assessed_fees.amount) AS total_assessed,
                SUM(COALESCE(posted_payments.amount_paid, 0)) AS total_paid,
                SUM(assessed_fees.amount - COALESCE(posted_payments.amount_paid, 0)) AS balance
            ", false)
            ->from('students')
            ->join(
                'student_enrollments AS enrollments',
                'enrollments.student_id = students.id',
                'inner'
            )
            ->join(
                $this->assessment_table . ' AS assessed_fees',
                'assessed_fees.enrollment_id = enrollments.id',
                'inner'
            )
            ->join(
                '(SELECT enrollment_fee_id, SUM(amount_paid) AS amount_paid
                  FROM ' . $this->payments_table . '
                  WHERE status = "posted"
                  GROUP BY enrollment_fee_id) AS posted_payments',
                'posted_payments.enrollment_fee_id = assessed_fees.id',
                'left',
                false
            )
            ->where('students.id', (int) $student_id)
            ->where('students.deleted_at IS NULL', null, false);

        if ($academic_year !== null && $academic_year !== '') {
            $this->db->where('enrollments.academic_year', $academic_year);
        }

        return $this->db
            ->group_by('students.id')
            ->get()
            ->row();
    }

    /** Return unpaid fee items belonging to the selected student. */
    public function get_outstanding_fees_by_student($student_id, $academic_year = null)
    {
        $this->db->reset_query();

        $this->db
            ->select('
                assessed_fees.id AS enrollment_fee_id,
                assessed_fees.enrollment_id,
                assessed_fees.fee_category,
                assessed_fees.payment_label,
                assessed_fees.payment_date,
                assessed_fees.amount AS amount_due,
                enrollments.academic_year,
                enrollments.grade_level,
                enrollments.section,
                COALESCE(posted_payments.amount_paid, 0) AS amount_paid,
                assessed_fees.amount - COALESCE(posted_payments.amount_paid, 0) AS balance
            ', false)
            ->from($this->assessment_table . ' AS assessed_fees')
            ->join(
                'student_enrollments AS enrollments',
                'enrollments.id = assessed_fees.enrollment_id',
                'inner'
            )
            ->join(
                '(SELECT enrollment_fee_id, SUM(amount_paid) AS amount_paid
                  FROM ' . $this->payments_table . '
                  WHERE status = "posted"
                  GROUP BY enrollment_fee_id) AS posted_payments',
                'posted_payments.enrollment_fee_id = assessed_fees.id',
                'left',
                false
            )
            ->where('enrollments.student_id', (int) $student_id)
            ->where('assessed_fees.amount > COALESCE(posted_payments.amount_paid, 0)', null, false);

        if ($academic_year !== null && $academic_year !== '') {
            $this->db->where('enrollments.academic_year', $academic_year);
        }

        return $this->db
            ->order_by('enrollments.academic_year', 'DESC')
            ->order_by('assessed_fees.payment_date', 'ASC')
            ->order_by('assessed_fees.payment_sequence', 'ASC')
            ->get()
            ->result();
    }

    /** Return the payment ledger for one student, newest payment first. */
    public function get_payment_history_by_student($student_id, $academic_year = null)
    {
        $this->db->reset_query();

        $this->db
            ->select('
                payments.id,
                payments.receipt_number,
                payments.amount_paid,
                payments.payment_method,
                payments.reference_number,
                payments.paid_at,
                payments.status,
                payments.enrollment_fee_id,
                (SELECT audit.reason FROM student_fee_payment_audit AS audit
                 WHERE audit.payment_id = payments.id AND audit.action = "void"
                 ORDER BY audit.id DESC LIMIT 1) AS void_reason,
                assessed_fees.fee_category,
                assessed_fees.payment_label,
                assessed_fees.amount AS assessed_amount,
                enrollments.academic_year,
                enrollments.grade_level,
                enrollments.payment_mode
            ')
            ->from($this->payments_table . ' AS payments')
            ->join(
                $this->assessment_table . ' AS assessed_fees',
                'assessed_fees.id = payments.enrollment_fee_id',
                'inner'
            )
            ->join(
                'student_enrollments AS enrollments',
                'enrollments.id = assessed_fees.enrollment_id',
                'inner'
            )
            ->where('enrollments.student_id', (int) $student_id);

        if ($academic_year !== null && $academic_year !== '') {
            $this->db->where('enrollments.academic_year', $academic_year);
        }

        return $this->db
            ->order_by('payments.paid_at', 'DESC')
            ->order_by('payments.id', 'DESC')
            ->get()
            ->result();
    }

    /** Return the preferred guardian contact for a payment statement. */
    public function get_statement_guardian($student_id)
    {
        $this->db->reset_query();

        return $this->db
            ->select('first_name, last_name, email')
            ->from('student_guardians')
            ->where('student_id', (int) $student_id)
            ->where('email IS NOT NULL', null, false)
            ->where('email !=', '')
            ->order_by('is_primary', 'DESC')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row();
    }

    /** Record a payment without allowing it to exceed the assessed balance. */
    public function record_payment(
        $enrollment_fee_id,
        $student_id,
        $amount,
        $payment_method,
        $reference_number,
        $recorded_by
    ) {
        $this->db->trans_begin();

        $assessment = $this->db->query(
            'SELECT assessed_fees.id, assessed_fees.amount, enrollments.student_id
             FROM student_enrollment_fees AS assessed_fees
             INNER JOIN student_enrollments AS enrollments
                ON enrollments.id = assessed_fees.enrollment_id
             WHERE assessed_fees.id = ?
             FOR UPDATE',
            [(int) $enrollment_fee_id]
        )->row();

        if (!$assessment || (int) $assessment->student_id !== (int) $student_id) {
            return $this->rollback_with_error('assessment_not_found');
        }

        $paid = $this->db
            ->select_sum('amount_paid', 'amount_paid')
            ->where('enrollment_fee_id', (int) $enrollment_fee_id)
            ->where('status', 'posted')
            ->get($this->payments_table)
            ->row();
        $balance = (float) $assessment->amount - (float) ($paid->amount_paid ?? 0);
        $amount = round((float) $amount, 2);

        if ($amount <= 0) {
            return $this->rollback_with_error('invalid_amount');
        }
        if ($amount > $balance) {
            return $this->rollback_with_error('amount_exceeds_balance');
        }

        $inserted = $this->db->insert($this->payments_table, [
            'enrollment_fee_id' => (int) $enrollment_fee_id,
            'receipt_number' => $this->create_receipt_number(),
            'amount_paid' => $amount,
            'payment_method' => $payment_method,
            'reference_number' => $reference_number !== '' ? $reference_number : null,
            'paid_at' => date('Y-m-d H:i:s'),
            'status' => 'posted',
            'recorded_by' => $recorded_by ? (int) $recorded_by : null
        ]);

        if (!$inserted || $this->db->trans_status() === false) {
            return $this->rollback_with_error('save_failed');
        }

        $payment_id = (int) $this->db->insert_id();
        $this->db->trans_commit();

        return [
            'ok' => true,
            'payment_id' => $payment_id
        ];
    }

    public function void_payment($payment_id, $student_id, $reason, $performed_by)
    {
        $this->db->trans_begin();
        $payment = $this->db->query(
            'SELECT payments.id, payments.status
             FROM ' . $this->payments_table . ' AS payments
             INNER JOIN ' . $this->assessment_table . ' AS assessed_fees
                ON assessed_fees.id = payments.enrollment_fee_id
             INNER JOIN student_enrollments AS enrollments
                ON enrollments.id = assessed_fees.enrollment_id
             WHERE payments.id = ? AND enrollments.student_id = ?
             FOR UPDATE',
            [(int) $payment_id, (int) $student_id]
        )->row();
        if (!$payment) return $this->rollback_with_error('payment_not_found');
        if ($payment->status !== 'posted') return $this->rollback_with_error('payment_not_posted');

        $audit_saved = $this->db->insert('student_fee_payment_audit', [
            'payment_id' => (int) $payment_id,
            'action' => 'void',
            'reason' => trim($reason),
            'performed_by' => $performed_by ? (int) $performed_by : null
        ]);
        $updated = $this->db->where('id', (int) $payment_id)->where('status', 'posted')
            ->update($this->payments_table, ['status' => 'voided']);
        if (!$audit_saved || !$updated || $this->db->trans_status() === false) {
            return $this->rollback_with_error('save_failed');
        }
        $this->db->trans_commit();
        return ['ok' => true, 'code' => 'voided'];
    }

    public function correct_payment_details($payment_id, $student_id, $payment_method, $reference_number, $reason, $performed_by)
    {
        $this->db->trans_begin();
        $payment = $this->db->query(
            'SELECT payments.id, payments.status, payments.payment_method, payments.reference_number
             FROM ' . $this->payments_table . ' AS payments
             INNER JOIN ' . $this->assessment_table . ' AS assessed_fees
                ON assessed_fees.id = payments.enrollment_fee_id
             INNER JOIN student_enrollments AS enrollments
                ON enrollments.id = assessed_fees.enrollment_id
             WHERE payments.id = ? AND enrollments.student_id = ?
             FOR UPDATE',
            [(int) $payment_id, (int) $student_id]
        )->row();
        if (!$payment) return $this->rollback_with_error('payment_not_found');
        if ($payment->status !== 'posted') return $this->rollback_with_error('payment_not_posted');

        $changes = [];
        if ((string) $payment->payment_method !== (string) $payment_method) {
            $changes['payment_method'] = [$payment->payment_method, $payment_method];
        }
        $old_reference = (string) ($payment->reference_number ?? '');
        if ($old_reference !== (string) $reference_number) {
            $changes['reference_number'] = [$old_reference, (string) $reference_number];
        }
        if (!$changes) return $this->rollback_with_error('no_changes');

        foreach ($changes as $field => $values) {
            if (!$this->db->insert('student_fee_payment_audit', [
                'payment_id' => (int) $payment_id,
                'action' => 'correction',
                'field_name' => $field,
                'old_value' => $values[0] !== '' ? $values[0] : null,
                'new_value' => $values[1] !== '' ? $values[1] : null,
                'reason' => trim($reason),
                'performed_by' => $performed_by ? (int) $performed_by : null
            ])) return $this->rollback_with_error('save_failed');
        }
        $updated = $this->db->where('id', (int) $payment_id)->where('status', 'posted')
            ->update($this->payments_table, [
                'payment_method' => $payment_method,
                'reference_number' => $reference_number !== '' ? $reference_number : null
            ]);
        if (!$updated || $this->db->trans_status() === false) return $this->rollback_with_error('save_failed');
        $this->db->trans_commit();
        return ['ok' => true, 'code' => 'corrected'];
    }

    public function get_payment_audit_by_payment_ids(array $payment_ids)
    {
        if (!$payment_ids) return [];
        $rows = $this->db
            ->select('payment_audit.*, users.first_name, users.last_name, users.username')
            ->from('student_fee_payment_audit AS payment_audit')
            ->join('users', 'users.id = payment_audit.performed_by', 'left')
            ->where_in('payment_audit.payment_id', array_map('intval', $payment_ids))
            ->order_by('payment_audit.performed_at', 'DESC')
            ->order_by('payment_audit.id', 'DESC')
            ->get()->result();
        foreach ($rows as $row) {
            $full_name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
            $row->performed_by_name = $full_name !== ''
                ? $full_name
                : (($row->username ?? '') !== '' ? $row->username : 'Unknown user');
        }
        $grouped = [];
        foreach ($rows as $row) $grouped[(int) $row->payment_id][] = $row;
        return $grouped;
    }

    private function create_receipt_number()
    {
        return 'PLLA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    private function rollback_with_error($code)
    {
        $this->db->trans_rollback();

        return [
            'ok' => false,
            'code' => $code
        ];
    }
}
