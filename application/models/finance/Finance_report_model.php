<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Read-only queries for student fee collection and balance reports. */
class Finance_report_model extends CI_Model
{
    /**
     * Return current assessed/paid/outstanding totals. The date range limits
     * collected amounts only; outstanding balances remain current balances.
     */
    public function get_report(array $filters)
    {
        $gradeLabel = "CASE
            WHEN UPPER(enrollments.grade_level) IN ('N', 'NURSERY') THEN 'Nursery'
            WHEN UPPER(enrollments.grade_level) IN ('K', 'KINDERGARTEN') THEN 'Kindergarten'
            WHEN enrollments.grade_level LIKE 'Grade %' THEN enrollments.grade_level
            ELSE CONCAT('Grade ', enrollments.grade_level)
        END";

        $postedTotals = '(SELECT enrollment_fee_id, SUM(amount_paid) AS amount_paid
            FROM student_fee_payments
            WHERE status = "posted"
            GROUP BY enrollment_fee_id) AS posted_totals';
        $periodTotals = '(SELECT enrollment_fee_id, SUM(amount_paid) AS amount_paid
            FROM student_fee_payments
            WHERE status = "posted"';
        if (!empty($filters['date_from'])) {
            $periodTotals .= ' AND paid_at >= ' . $this->db->escape($filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $periodTotals .= ' AND paid_at < DATE_ADD(' . $this->db->escape($filters['date_to']) . ', INTERVAL 1 DAY)';
        }
        $periodTotals .= ' GROUP BY enrollment_fee_id) AS period_totals';

        $this->db->reset_query();
        $this->db
            ->select('enrollments.academic_year AS school_year', false)
            ->select($gradeLabel . ' AS grade', false)
            ->select("COALESCE(NULLIF(enrollments.section, ''), 'Unassigned') AS section", false)
            ->select('SUM(assessed_fees.amount) AS assessed', false)
            ->select('SUM(COALESCE(period_totals.amount_paid, 0)) AS collected', false)
            ->select('SUM(GREATEST(assessed_fees.amount - COALESCE(posted_totals.amount_paid, 0), 0)) AS outstanding', false)
            ->from('student_enrollments AS enrollments')
            ->join('students', 'students.id = enrollments.student_id', 'inner')
            ->join('student_enrollment_fees AS assessed_fees', 'assessed_fees.enrollment_id = enrollments.id', 'inner')
            ->join($postedTotals, 'posted_totals.enrollment_fee_id = assessed_fees.id', 'left', false)
            ->join($periodTotals, 'period_totals.enrollment_fee_id = assessed_fees.id', 'left', false)
            ->where('students.deleted_at IS NULL', null, false);

        if (!empty($filters['school_year'])) {
            $this->db->where('enrollments.academic_year', $filters['school_year']);
        }
        if (!empty($filters['grade'])) {
            $gradeValues = !empty($filters['grade_values']) ? $filters['grade_values'] : [$filters['grade']];
            $this->db->group_start();
            foreach ($gradeValues as $index => $gradeValue) {
                if ($index === 0) $this->db->where('enrollments.grade_level', $gradeValue);
                else $this->db->or_where('enrollments.grade_level', $gradeValue);
            }
            $this->db->group_end();
        }
        if (!empty($filters['section'])) {
            $this->db->where('enrollments.section', $filters['section']);
        }

        $rows = $this->db
            ->group_by(['enrollments.academic_year', 'grade', 'section'])
            ->order_by('enrollments.academic_year', 'DESC')
            ->order_by('grade', 'ASC')
            ->order_by('section', 'ASC')
            ->get()
            ->result();

        $totals = ['assessed' => 0.0, 'collected' => 0.0, 'outstanding' => 0.0];
        foreach ($rows as $row) {
            foreach ($totals as $key => $unused) $totals[$key] += (float) $row->$key;
        }
        return ['rows' => $rows] + $totals;
    }
}
