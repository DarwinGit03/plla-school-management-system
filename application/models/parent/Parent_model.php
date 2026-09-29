<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Data access for parent portal records. */
class Parent_model extends CI_Model
{
    /** Return payment records for students linked by guardian email. */
    public function get_payment_history_by_guardian_email($email)
    {
        return $this->db
            ->distinct()
            ->select('
                payments.id,
                payments.receipt_number,
                payments.amount_paid,
                payments.payment_method,
                payments.reference_number,
                payments.paid_at,
                payments.status,
                (SELECT audit.reason FROM student_fee_payment_audit AS audit
                 WHERE audit.payment_id = payments.id AND audit.action = "void"
                 ORDER BY audit.id DESC LIMIT 1) AS void_reason,
                students.id AS student_id,
                students.student_no,
                students.first_name,
                students.middle_name,
                students.last_name,
                students.suffix,
                enrollments.academic_year,
                enrollments.grade_level,
                assessed_fees.fee_category,
                assessed_fees.payment_label
            ')
            ->from('student_fee_payments AS payments')
            ->join(
                'student_enrollment_fees AS assessed_fees',
                'assessed_fees.id = payments.enrollment_fee_id',
                'inner'
            )
            ->join(
                'student_enrollments AS enrollments',
                'enrollments.id = assessed_fees.enrollment_id',
                'inner'
            )
            ->join(
                'students',
                'students.id = enrollments.student_id',
                'inner'
            )
            ->join(
                'student_guardians AS guardians',
                'guardians.student_id = students.id',
                'inner'
            )
            ->where('guardians.email', trim((string) $email))
            ->where('students.deleted_at IS NULL', null, false)
            ->order_by('payments.paid_at', 'DESC')
            ->order_by('payments.id', 'DESC')
            ->get()
            ->result();
    }
}
