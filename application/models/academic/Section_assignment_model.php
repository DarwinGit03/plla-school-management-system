<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Section_assignment_model extends CI_Model
{
    public function get_active_sections($year, $grade)
    {
        return $this->db
            ->where('year', $year)
            ->where('grade', $grade)
            ->where('status', 'Active')
            ->order_by('section', 'ASC')
            ->get('academic_sections')
            ->result();
    }

    public function get_section($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->get('academic_sections')
            ->row();
    }

    public function get_students_for_assignment($year, $grade, $search = '')
    {
        $this->db
            ->select('enrollments.id AS enrollment_id, enrollments.student_id, enrollments.academic_year, enrollments.grade_level, enrollments.section_id, COALESCE(NULLIF(TRIM(enrollments.section), ""), assigned_section.section) AS current_section, students.student_no, students.lrn, students.first_name, students.middle_name, students.last_name, students.suffix', false)
            ->from('student_enrollments AS enrollments')
            ->join('students', 'students.id = enrollments.student_id', 'inner')
            ->join('academic_sections AS assigned_section', 'assigned_section.id = enrollments.section_id', 'left')
            ->where('students.deleted_at IS NULL', null, false)
            ->where('enrollments.academic_year', $year)
            ->where('enrollments.status', 'active');

        $this->apply_grade_filter($grade, 'enrollments.grade_level');
        $this->apply_student_search($search);

        return $this->db
            ->order_by('students.last_name', 'ASC')
            ->order_by('students.first_name', 'ASC')
            ->order_by('students.student_no', 'ASC')
            ->get()
            ->result();
    }

    public function is_active_enrollment_for_assignment($enrollmentId, $year, $grade)
    {
        $this->db
            ->select('enrollments.id')
            ->from('student_enrollments AS enrollments')
            ->join('students', 'students.id = enrollments.student_id', 'inner')
            ->where('enrollments.id', (int) $enrollmentId)
            ->where('enrollments.academic_year', $year)
            ->where('enrollments.status', 'active')
            ->where('students.deleted_at IS NULL', null, false);
        $this->apply_grade_filter($grade, 'enrollments.grade_level');

        return $this->db->limit(1)->get()->num_rows() === 1;
    }

    public function assign_enrollments(array $enrollment_ids, $section, $employee_no)
    {
        $enrollment_ids = array_values(array_unique(array_map('intval', $enrollment_ids)));
        if (!$enrollment_ids) return ['ok' => false, 'code' => 'empty_selection'];

        $this->db->trans_begin();

        $this->db
            ->select('enrollments.id')
            ->from('student_enrollments AS enrollments')
            ->where_in('enrollments.id', $enrollment_ids)
            ->where('enrollments.academic_year', $section->year)
            ->where('enrollments.status', 'active');
        $this->apply_grade_filter($section->grade, 'enrollments.grade_level');
        $eligible_query = $this->db->get_compiled_select();
        $eligible_rows = $this->db->query($eligible_query . ' FOR UPDATE')->result_array();

        if (count($eligible_rows) !== count($enrollment_ids)) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'selection_changed'];
        }

        $this->db
            ->where_in('id', $enrollment_ids)
            ->where('academic_year', $section->year)
            ->where('status', 'active');
        $this->apply_grade_filter($section->grade, 'grade_level');
        $this->db
            ->set([
                'section_id' => (int) $section->id,
                'section' => $section->section,
                'updated_by' => (string) $employee_no
            ])
            ->set('updated_at', 'CURRENT_TIMESTAMP', false)
            ->update('student_enrollments');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'assignment_failed'];
        }

        $this->db->trans_commit();
        return ['ok' => true, 'count' => count($enrollment_ids)];
    }

    private function apply_grade_filter($grade, $field)
    {
        $grade = trim((string) $grade);
        $label = $this->grade_label($grade);
        $code = $this->grade_code($grade);
        $values = array_values(array_unique(array_filter([$grade, $label, $code], 'strlen')));

        $this->db->group_start();
        foreach ($values as $index => $value) {
            if ($index === 0) $this->db->where($field, $value);
            else $this->db->or_where($field, $value);
        }
        $this->db->group_end();
    }

    private function apply_student_search($search)
    {
        $search = trim((string) $search);
        if ($search === '') return;

        $this->db->group_start()
            ->like('students.student_no', $search)
            ->or_like('students.lrn', $search)
            ->or_like('students.first_name', $search)
            ->or_like('students.middle_name', $search)
            ->or_like('students.last_name', $search)
            ->group_end();
    }

    private function grade_code($grade)
    {
        $grade = trim((string) $grade);
        if (strcasecmp($grade, 'Nursery') === 0) return 'N';
        if (strcasecmp($grade, 'Kindergarten') === 0) return 'K';
        if (preg_match('/^Grade\s*(.+)$/i', $grade, $matches)) return trim($matches[1]);
        return $grade;
    }

    private function grade_label($grade)
    {
        $grade = trim((string) $grade);
        if (strcasecmp($grade, 'N') === 0 || strcasecmp($grade, 'Nursery') === 0) return 'Nursery';
        if (strcasecmp($grade, 'K') === 0 || strcasecmp($grade, 'Kindergarten') === 0) return 'Kindergarten';
        if (preg_match('/^Grade\s*(.+)$/i', $grade, $matches)) return 'Grade ' . trim($matches[1]);
        return ctype_digit($grade) ? 'Grade ' . $grade : $grade;
    }
}
