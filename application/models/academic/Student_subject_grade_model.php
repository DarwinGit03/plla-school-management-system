<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_subject_grade_model extends CI_Model
{
    public function get_for_assignment($assignmentId)
    {
        $rows = $this->db
            ->where('assignment_id', (int) $assignmentId)
            ->get('student_subject_grades')
            ->result();

        $grades = [];
        foreach ($rows as $row) {
            $grades[(int) $row->enrollment_id] = $row;
        }

        return $grades;
    }

    public function save_for_enrollment($assignmentId, $enrollmentId, array $grades, $userId)
    {
        $values = [
            'prelim_grade' => $grades['prelim_grade'],
            'midterm_grade' => $grades['midterm_grade'],
            'finals_grade' => $grades['finals_grade'],
            'final_grade' => $grades['final_grade'],
            'updated_by' => (int) $userId
        ];

        $existing = $this->db
            ->select('id')
            ->where('assignment_id', (int) $assignmentId)
            ->where('enrollment_id', (int) $enrollmentId)
            ->get('student_subject_grades')
            ->row();

        if ($existing) {
            return $this->db->where('id', (int) $existing->id)->update('student_subject_grades', $values);
        }

        $values['assignment_id'] = (int) $assignmentId;
        $values['enrollment_id'] = (int) $enrollmentId;
        return $this->db->insert('student_subject_grades', $values);
    }
}
