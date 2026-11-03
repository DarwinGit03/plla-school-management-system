<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Data access for parent portal records. */
class Parent_model extends CI_Model
{
    public function has_guardian_email($email)
    {
        $email = strtolower(trim((string) $email));
        return $this->db
            ->where('LOWER(TRIM(email)) = ' . $this->db->escape($email), null, false)
            ->count_all_results('student_guardians') > 0;
    }

    public function get_guardian_identity_by_email($email)
    {
        $email = strtolower(trim((string) $email));
        return $this->db
            ->select('first_name, middle_name, last_name')
            ->from('student_guardians')
            ->where('LOWER(TRIM(email)) = ' . $this->db->escape($email), null, false)
            ->order_by('is_primary', 'DESC')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row();
    }

    /** Return every non-deleted student linked to this guardian email. */
    public function get_students_by_guardian_email($email)
    {
        $email = strtolower(trim((string) $email));
        return $this->db
            ->select('students.id, students.student_no, students.first_name, students.middle_name, students.last_name, students.suffix,
                enrollments.id AS current_enrollment_id, enrollments.academic_year, enrollments.grade_level, enrollments.section,
                enrollments.status AS enrollment_status')
            ->from('students')
            ->join('student_enrollments AS enrollments', 'enrollments.student_id = students.id AND enrollments.status = "active" AND enrollments.id = (SELECT e2.id FROM student_enrollments e2 WHERE e2.student_id = students.id AND e2.status = "active" ORDER BY e2.academic_year DESC, e2.id DESC LIMIT 1)', 'left', false)
            ->where('EXISTS (SELECT 1 FROM student_guardians guardians WHERE guardians.student_id = students.id AND LOWER(TRIM(guardians.email)) = ' . $this->db->escape($email) . ')', null, false)
            ->where('students.deleted_at IS NULL', null, false)
            ->order_by('students.last_name', 'ASC')
            ->order_by('students.first_name', 'ASC')
            ->get()
            ->result();
    }

    /** Resolve a student only when the supplied guardian email is linked to them. */
    public function get_linked_student($email, $studentId)
    {
        $email = strtolower(trim((string) $email));
        return $this->db->select('students.*')
            ->from('students')
            ->where('students.id', (int) $studentId)
            ->where('EXISTS (SELECT 1 FROM student_guardians guardians WHERE guardians.student_id = students.id AND LOWER(TRIM(guardians.email)) = ' . $this->db->escape($email) . ')', null, false)
            ->where('students.deleted_at IS NULL', null, false)
            ->get()->row();
    }

    /** Enrollments available for parent report viewing; mirrors staff report-card availability. */
    public function get_student_enrollments($studentId)
    {
        return $this->db->select('e.id, e.academic_year, e.grade_level, e.section, e.section_id, e.status,
                COALESCE(sec.section, e.section) AS section_name', false)
            ->from('student_enrollments e')
            ->join('academic_sections sec', 'sec.id = e.section_id', 'left')
            ->where('e.student_id', (int) $studentId)
            ->where_in('e.status', ['active', 'completed'])
            ->order_by('e.academic_year', 'DESC')
            ->order_by('e.id', 'DESC')
            ->get()->result();
    }

    /** Fee items and posted payment totals for one enrollment belonging to a linked child. */
    public function get_fee_items_for_enrollment($studentId, $enrollmentId)
    {
        return $this->db->select('fees.id AS enrollment_fee_id, fees.fee_category, fees.payment_label,
                fees.payment_date, fees.amount AS amount_due,
                COALESCE(posted.amount_paid, 0) AS amount_paid,
                GREATEST(fees.amount - COALESCE(posted.amount_paid, 0), 0) AS balance', false)
            ->from('student_enrollment_fees fees')
            ->join('student_enrollments e', 'e.id = fees.enrollment_id', 'inner')
            ->join('(SELECT enrollment_fee_id, SUM(amount_paid) AS amount_paid FROM student_fee_payments WHERE status = "posted" GROUP BY enrollment_fee_id) posted', 'posted.enrollment_fee_id = fees.id', 'left', false)
            ->where('e.id', (int) $enrollmentId)
            ->where('e.student_id', (int) $studentId)
            ->order_by('fees.fee_category', 'ASC')
            ->order_by('fees.payment_date', 'ASC')
            ->order_by('fees.id', 'ASC')
            ->get()->result();
    }

    public function get_parent_login_code($email)
    {
        return $this->db->where('email', strtolower(trim((string) $email)))
            ->get('parent_portal_login_codes')->row();
    }

    public function save_parent_login_code($email, $codeHash, $expiresAt)
    {
        $email = strtolower(trim((string) $email));
        $data = [
            'code_hash' => $codeHash,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'sent_at' => date('Y-m-d H:i:s')
        ];
        $existing = $this->get_parent_login_code($email);
        if ($existing) return $this->db->where('email', $email)->update('parent_portal_login_codes', $data);
        $data['email'] = $email;
        return $this->db->insert('parent_portal_login_codes', $data);
    }

    public function increment_parent_login_attempts($email)
    {
        $this->db->set('attempts', 'attempts + 1', false);
        return $this->db->where('email', strtolower(trim((string) $email)))
            ->update('parent_portal_login_codes');
    }

    public function delete_parent_login_code($email)
    {
        return $this->db->where('email', strtolower(trim((string) $email)))
            ->delete('parent_portal_login_codes');
    }

    /** Return payment records for students linked by guardian email. */
    public function get_payment_history_by_guardian_email($email, $studentId = null, $schoolYear = null)
    {
        $email = strtolower(trim((string) $email));
        $this->db
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
            ->where('LOWER(TRIM(guardians.email)) = ' . $this->db->escape($email), null, false)
            ->where('students.deleted_at IS NULL', null, false);
        if ($studentId !== null) $this->db->where('students.id', (int) $studentId);
        if ($schoolYear !== null && trim((string) $schoolYear) !== '') $this->db->where('enrollments.academic_year', trim((string) $schoolYear));
        return $this->db->order_by('payments.paid_at', 'DESC')->order_by('payments.id', 'DESC')->get()->result();
    }
}
