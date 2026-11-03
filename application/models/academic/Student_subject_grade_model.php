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
            ->select('id, review_status')
            ->where('assignment_id', (int) $assignmentId)
            ->where('enrollment_id', (int) $enrollmentId)
            ->get('student_subject_grades')
            ->row();

        if ($existing && $existing->review_status === 'approved') {
            return ['ok' => false, 'code' => 'approved_locked'];
        }

        if ($existing) {
            $values['review_status'] = 'pending_review';
            $values['submitted_at'] = date('Y-m-d H:i:s');
            $values['reviewed_by'] = null;
            $values['reviewed_at'] = null;
            $values['review_note'] = null;
            $saved = $this->db->where('id', (int) $existing->id)->update('student_subject_grades', $values);
            return ['ok' => (bool) $saved, 'code' => $saved ? 'saved' : 'save_failed', 'id' => (int) $existing->id];
        }

        $values['assignment_id'] = (int) $assignmentId;
        $values['enrollment_id'] = (int) $enrollmentId;
        $values['review_status'] = 'pending_review';
        $values['submitted_at'] = date('Y-m-d H:i:s');
        $saved = $this->db->insert('student_subject_grades', $values);
        return ['ok' => (bool) $saved, 'code' => $saved ? 'saved' : 'save_failed', 'id' => (int) $this->db->insert_id()];
    }

    public function get_review_queue()
    {
        return $this->db
            ->select('grades.*, assignments.employee_id, assignments.id AS assignment_id, subjects.subject_name, subjects.subject_code, subjects.school_year, subjects.grade_code, enrollments.student_id, enrollments.academic_year, enrollments.grade_level, students.student_no, students.first_name, students.middle_name, students.last_name', false)
            ->from('student_subject_grades AS grades')
            ->join('employee_subject_assignments AS assignments', 'assignments.id = grades.assignment_id')
            ->join('academic_subjects AS subjects', 'subjects.id = assignments.subject_id')
            ->join('student_enrollments AS enrollments', 'enrollments.id = grades.enrollment_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->where('grades.review_status', 'pending_review')
            ->where('students.deleted_at IS NULL', null, false)
            ->order_by('grades.submitted_at', 'ASC')
            ->get()->result();
    }

    public function get_grade($id)
    {
        return $this->db->where('id', (int) $id)->get('student_subject_grades')->row();
    }

    public function decide_review($id, $reviewerId, $decision, $note)
    {
        if (!in_array($decision, ['approved', 'returned'], true)) return false;
        return $this->db->where('id', (int) $id)
            ->where('review_status', 'pending_review')
            ->update('student_subject_grades', [
                'review_status' => $decision,
                'reviewed_by' => (int) $reviewerId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'review_note' => $note !== '' ? $note : null
            ]);
    }

    public function request_correction($gradeId, array $grades, $reason, $userId)
    {
        $grade = $this->get_grade($gradeId);
        if (!$grade || $grade->review_status !== 'approved') return ['ok' => false, 'code' => 'grade_not_approved'];
        $pending = $this->db->where('record_type', 'grade')->where('record_id', (int) $gradeId)
            ->where('status', 'pending')->count_all_results('academic_correction_requests');
        if ($pending) return ['ok' => false, 'code' => 'request_pending'];
        $ok = $this->db->insert('academic_correction_requests', [
            'record_type' => 'grade', 'record_id' => (int) $gradeId,
            'proposed_values' => json_encode($grades), 'reason' => trim((string) $reason),
            'requested_by' => (int) $userId
        ]);
        return ['ok' => (bool) $ok, 'code' => $ok ? 'saved' : 'save_failed', 'id' => (int) $this->db->insert_id()];
    }

    public function get_correction_queue($recordType = '')
    {
        $this->db->where('status', 'pending');
        if ($recordType !== '') $this->db->where('record_type', $recordType);
        return $this->db->order_by('requested_at', 'ASC')->get('academic_correction_requests')->result();
    }

    public function get_correction_request($requestId)
    {
        return $this->db->where('id', (int) $requestId)->get('academic_correction_requests')->row();
    }

    public function decide_grade_correction($requestId, $reviewerId, $approve, $note)
    {
        $this->db->trans_begin();
        $request = $this->db->query('SELECT * FROM academic_correction_requests WHERE id = ? AND record_type = "grade" AND status = "pending" FOR UPDATE', [(int) $requestId])->row();
        if (!$request) { $this->db->trans_rollback(); return false; }
        $values = json_decode($request->proposed_values, true);
        if ($approve && !is_array($values)) { $this->db->trans_rollback(); return false; }
        if ($approve && (!isset($values['final_grade']) || $values['final_grade'] === null || $values['final_grade'] === '')) { $this->db->trans_rollback(); return false; }
        if ($approve && is_array($values)) {
            $updated = $this->db->where('id', (int) $request->record_id)->update('student_subject_grades', [
                'prelim_grade' => $values['prelim_grade'] ?? null,
                'midterm_grade' => $values['midterm_grade'] ?? null,
                'finals_grade' => $values['finals_grade'] ?? null,
                'final_grade' => $values['final_grade'] ?? null,
                'reviewed_by' => (int) $reviewerId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'review_note' => $note !== '' ? $note : 'Approved correction request.',
                'updated_by' => (int) $reviewerId
            ]);
            if (!$updated) { $this->db->trans_rollback(); return false; }
        }
        $ok = $this->db->where('id', (int) $requestId)->where('status', 'pending')->update('academic_correction_requests', [
            'status' => $approve ? 'approved' : 'rejected',
            'reviewed_by' => (int) $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_note' => $note !== '' ? $note : null
        ]);
        if (!$ok || $this->db->trans_status() === false) { $this->db->trans_rollback(); return false; }
        $this->db->trans_commit();
        return $request;
    }
}
