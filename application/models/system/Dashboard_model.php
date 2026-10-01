<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
    public function get_dashboard_school_year_preference($user_id)
    {
        $row = $this->db
            ->select('preference_value')
            ->where('user_id', (int) $user_id)
            ->where('preference_key', 'dashboard.school_year')
            ->get('user_preferences')
            ->row();

        return $row ? (string) $row->preference_value : '';
    }

    public function save_dashboard_school_year_preference($user_id, $school_year)
    {
        return $this->db->query(
            'INSERT INTO `user_preferences` (`user_id`, `preference_key`, `preference_value`, `updated_at`)
             VALUES (?, ?, ?, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE `preference_value` = VALUES(`preference_value`), `updated_at` = CURRENT_TIMESTAMP',
            [(int) $user_id, 'dashboard.school_year', (string) $school_year]
        );
    }

    public function count_active_students($school_year)
    {
        if ($school_year === '') return 0;

        return (int) $this->db
            ->select('COUNT(DISTINCT enrollments.student_id) AS total', false)
            ->from('student_enrollments AS enrollments')
            ->join('students', 'students.id = enrollments.student_id', 'inner')
            ->where('enrollments.academic_year', $school_year)
            ->where('enrollments.status', 'active')
            ->where('students.deleted_at IS NULL', null, false)
            ->get()
            ->row()
            ->total;
    }

    public function get_active_students_by_grade($school_year)
    {
        if ($school_year === '') return [];

        $gradeLabel = "CASE
            WHEN UPPER(enrollments.grade_level) IN ('N', 'NURSERY') THEN 'Nursery'
            WHEN UPPER(enrollments.grade_level) IN ('K', 'KINDERGARTEN') THEN 'Kindergarten'
            WHEN enrollments.grade_level LIKE 'Grade %' THEN enrollments.grade_level
            ELSE CONCAT('Grade ', enrollments.grade_level)
        END";

        return $this->db
            ->select($gradeLabel . ' AS grade', false)
            ->select('COUNT(DISTINCT enrollments.student_id) AS student_count', false)
            ->from('student_enrollments AS enrollments')
            ->join('students', 'students.id = enrollments.student_id', 'inner')
            ->where('enrollments.academic_year', $school_year)
            ->where('enrollments.status', 'active')
            ->where('students.deleted_at IS NULL', null, false)
            ->group_by('grade')
            ->order_by('grade', 'ASC')
            ->get()
            ->result();
    }

    public function count_active_employees()
    {
        return (int) $this->db
            ->where('status', 'active')
            ->count_all_results('employees');
    }
}
