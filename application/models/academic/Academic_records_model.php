<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Academic_records_model extends CI_Model
{
    public function get_sections_for_assignment($assignment)
    {
        return $this->db->select('sections.*')
            ->from('academic_sections AS sections')
            ->where('sections.year', $assignment->offering_school_year)
            ->where('sections.grade', $assignment->offering_grade_code)
            ->where('sections.status', 'Active')
            ->where('EXISTS (SELECT 1 FROM student_enrollments e WHERE e.academic_year = sections.year AND e.status = "active" AND (e.section_id = sections.id OR e.section = sections.section OR e.section = CAST(sections.id AS CHAR)) AND (e.grade_level = sections.grade OR e.grade_level = CONCAT("Grade ", sections.grade) OR (sections.grade = "N" AND e.grade_level IN ("N", "Nursery")) OR (sections.grade = "K" AND e.grade_level IN ("K", "Kindergarten"))))', null, false)
            ->order_by('sections.section', 'ASC')->get()->result();
    }

    public function get_students_for_section($assignment, $sectionId)
    {
        $grade = '((e.grade_level = s.grade) OR (e.grade_level = CONCAT("Grade ", s.grade)) OR (s.grade = "N" AND e.grade_level IN ("N", "Nursery")) OR (s.grade = "K" AND e.grade_level IN ("K", "Kindergarten")))';
        return $this->db->select('e.id AS enrollment_id, e.student_id, e.section_id, s.section, st.student_no, st.first_name, st.middle_name, st.last_name')
            ->from('student_enrollments e')->join('students st', 'st.id = e.student_id')
            ->join('academic_sections s', 's.id = ' . (int) $sectionId, 'inner')
            ->where('e.academic_year', $assignment->offering_school_year)->where('e.status', 'active')
            ->where('s.id', (int) $sectionId)->where('s.status', 'Active')
            ->where('(e.section_id = s.id OR e.section = s.section OR e.section = CAST(s.id AS CHAR))', null, false)
            ->where($grade, null, false)->where('st.deleted_at IS NULL', null, false)
            ->order_by('st.last_name', 'ASC')->order_by('st.first_name', 'ASC')->get()->result();
    }

    public function get_attendance_session($assignmentId, $sectionId, $date)
    {
        return $this->db->where('assignment_id', (int) $assignmentId)->where('section_id', (int) $sectionId)
            ->where('attendance_date', $date)->get('student_attendance_sessions')->row();
    }

    public function get_attendance_for_session($sessionId)
    {
        $rows = $this->db->where('session_id', (int) $sessionId)->get('student_attendance')->result();
        $result = [];
        foreach ($rows as $row) $result[(int) $row->enrollment_id] = $row;
        return $result;
    }

    public function save_initial_attendance($assignmentId, $sectionId, $date, array $statuses, $userId)
    {
        $this->db->trans_begin();
        $session = $this->get_attendance_session($assignmentId, $sectionId, $date);
        if ($session) { $this->db->trans_rollback(); return ['ok' => false, 'code' => 'session_exists']; }
        $this->db->insert('student_attendance_sessions', [
            'assignment_id' => (int) $assignmentId, 'section_id' => (int) $sectionId,
            'attendance_date' => $date, 'created_by' => (int) $userId
        ]);
        $sessionId = (int) $this->db->insert_id();
        foreach ($statuses as $enrollmentId => $status) {
            $ok = $this->db->insert('student_attendance', [
                'session_id' => $sessionId, 'enrollment_id' => (int) $enrollmentId,
                'attendance_status' => $status, 'updated_by' => (int) $userId
            ]);
            if (!$ok) { $this->db->trans_rollback(); return ['ok' => false, 'code' => 'save_failed']; }
        }
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return ['ok' => false, 'code' => 'save_failed']; }
        $this->db->trans_commit();
        return ['ok' => true, 'session_id' => $sessionId];
    }

    public function request_attendance_corrections(array $changes, $reason, $userId)
    {
        if (!$changes) return ['ok' => true, 'changes' => []];
        $recordIds = array_map(function ($change) { return (int) $change['record']->id; }, $changes);
        $this->db->trans_begin();
        $pending = $this->db->where('record_type', 'attendance')->where('status', 'pending')->where_in('record_id', $recordIds)
            ->get('academic_correction_requests')->result();
        if ($pending) { $this->db->trans_rollback(); return ['ok' => false, 'code' => 'request_pending']; }
        foreach ($changes as $change) {
            $ok = $this->db->insert('academic_correction_requests', [
                'record_type' => 'attendance', 'record_id' => (int) $change['record']->id,
                'proposed_values' => json_encode(['attendance_status' => $change['status']]),
                'reason' => trim((string) $reason), 'requested_by' => (int) $userId
            ]);
            if (!$ok) { $this->db->trans_rollback(); return ['ok' => false, 'code' => 'save_failed']; }
        }
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return ['ok' => false, 'code' => 'save_failed']; }
        $this->db->trans_commit();
        return ['ok' => true, 'changes' => $changes];
    }

    public function get_correction_queue($type)
    {
        $this->db->where('status', 'pending')->where('record_type', $type);
        return $this->db->order_by('requested_at', 'ASC')->get('academic_correction_requests')->result();
    }

    public function get_correction_context($request)
    {
        if ($request->record_type === 'grade') {
            return $this->db->select('g.*, s.subject_name, s.subject_code, s.school_year, s.grade_code, e.student_no, e.first_name, e.middle_name, e.last_name')
                ->from('student_subject_grades g')->join('employee_subject_assignments a', 'a.id = g.assignment_id')
                ->join('academic_subjects s', 's.id = a.subject_id')->join('student_enrollments se', 'se.id = g.enrollment_id')
                ->join('students e', 'e.id = se.student_id')->where('g.id', (int) $request->record_id)->get()->row();
        }
        return $this->db->select('a.*, ss.attendance_date, sec.section, sub.subject_name, en.student_no, st.first_name, st.middle_name, st.last_name')
            ->from('student_attendance a')->join('student_attendance_sessions ss', 'ss.id = a.session_id')
            ->join('employee_subject_assignments esa', 'esa.id = ss.assignment_id')->join('academic_subjects sub', 'sub.id = esa.subject_id')
            ->join('academic_sections sec', 'sec.id = ss.section_id')->join('student_enrollments en', 'en.id = a.enrollment_id')
            ->join('students st', 'st.id = en.student_id')->where('a.id', (int) $request->record_id)->get()->row();
    }

    public function decide_attendance_correction($requestId, $reviewerId, $approve, $note)
    {
        $this->db->trans_begin();
        $request = $this->db->query('SELECT * FROM academic_correction_requests WHERE id = ? AND record_type = "attendance" AND status = "pending" FOR UPDATE', [(int) $requestId])->row();
        if (!$request) { $this->db->trans_rollback(); return false; }
        $values = json_decode($request->proposed_values, true);
        if ($approve && (!is_array($values) || !in_array($values['attendance_status'] ?? '', ['present', 'absent', 'late', 'excused'], true))) { $this->db->trans_rollback(); return false; }
        if ($approve && isset($values['attendance_status'])) {
            $updated = $this->db->where('id', (int) $request->record_id)->update('student_attendance', [
                'attendance_status' => $values['attendance_status'], 'updated_by' => (int) $reviewerId
            ]);
            if (!$updated) { $this->db->trans_rollback(); return false; }
        }
        $ok = $this->db->where('id', (int) $requestId)->where('status', 'pending')->update('academic_correction_requests', [
            'status' => $approve ? 'approved' : 'rejected', 'reviewed_by' => (int) $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'), 'review_note' => $note !== '' ? $note : null
        ]);
        if (!$ok || $this->db->trans_status() === false) { $this->db->trans_rollback(); return false; }
        $this->db->trans_commit();
        return $request;
    }

    public function get_enrollments($schoolYear, $search = '')
    {
        $this->db->select('e.id AS enrollment_id, e.academic_year, e.grade_level, e.section, e.section_id, s.section AS section_name, st.student_no, st.first_name, st.middle_name, st.last_name')
            ->from('student_enrollments e')->join('students st', 'st.id = e.student_id')
            ->join('academic_sections s', 's.id = e.section_id', 'left')->where('e.academic_year', $schoolYear)
            ->where_in('e.status', ['active', 'completed'])->where('st.deleted_at IS NULL', null, false);
        if ($search !== '') $this->db->group_start()->like('st.student_no', $search)->or_like('st.first_name', $search)->or_like('st.last_name', $search)->or_like('st.lrn', $search)->group_end();
        return $this->db->order_by('st.last_name', 'ASC')->order_by('st.first_name', 'ASC')->get()->result();
    }

    public function get_report_card($enrollmentId)
    {
        $enrollment = $this->db->select('e.*, s.student_no, s.lrn, s.first_name, s.middle_name, s.last_name, COALESCE(sec.section, e.section) AS section_name', false)
            ->from('student_enrollments e')->join('students s', 's.id = e.student_id')->join('academic_sections sec', 'sec.id = e.section_id', 'left')
            ->where('e.id', (int) $enrollmentId)->where('s.deleted_at IS NULL', null, false)->get()->row();
        if (!$enrollment) return null;
        $studentEnrollmentId = (int) $enrollmentId;
        $grades = $this->db->select('sub.id AS subject_id, sub.subject_name, sub.subject_code, '
                . '(SELECT g.prelim_grade FROM student_subject_grades g JOIN employee_subject_assignments ga ON ga.id = g.assignment_id WHERE ga.subject_id = sub.id AND g.enrollment_id = ' . $studentEnrollmentId . ' ORDER BY g.updated_at DESC LIMIT 1) AS prelim_grade, '
                . '(SELECT g.midterm_grade FROM student_subject_grades g JOIN employee_subject_assignments ga ON ga.id = g.assignment_id WHERE ga.subject_id = sub.id AND g.enrollment_id = ' . $studentEnrollmentId . ' ORDER BY g.updated_at DESC LIMIT 1) AS midterm_grade, '
                . '(SELECT g.finals_grade FROM student_subject_grades g JOIN employee_subject_assignments ga ON ga.id = g.assignment_id WHERE ga.subject_id = sub.id AND g.enrollment_id = ' . $studentEnrollmentId . ' ORDER BY g.updated_at DESC LIMIT 1) AS finals_grade, '
                . '(SELECT g.final_grade FROM student_subject_grades g JOIN employee_subject_assignments ga ON ga.id = g.assignment_id WHERE ga.subject_id = sub.id AND g.enrollment_id = ' . $studentEnrollmentId . ' ORDER BY g.updated_at DESC LIMIT 1) AS final_grade, '
                . '(SELECT g.review_status FROM student_subject_grades g JOIN employee_subject_assignments ga ON ga.id = g.assignment_id WHERE ga.subject_id = sub.id AND g.enrollment_id = ' . $studentEnrollmentId . ' ORDER BY g.updated_at DESC LIMIT 1) AS review_status', false)
            ->from('academic_subjects sub')->where('sub.school_year', $enrollment->academic_year)
            ->where('sub.grade_code', $this->grade_code($enrollment->grade_level))
            ->where('EXISTS (SELECT 1 FROM employee_subject_assignments a WHERE a.subject_id = sub.id)', null, false)
            ->order_by('sub.subject_name', 'ASC')->get()->result();
        $attendance = $this->db->select('sub.subject_name, ar.attendance_status, COUNT(*) AS total')
            ->from('student_attendance ar')->join('student_attendance_sessions ses', 'ses.id = ar.session_id')
            ->join('employee_subject_assignments a', 'a.id = ses.assignment_id')->join('academic_subjects sub', 'sub.id = a.subject_id')
            ->where('ar.enrollment_id', (int) $enrollmentId)->where('sub.school_year', $enrollment->academic_year)
            ->group_by(['sub.subject_name', 'ar.attendance_status'])->get()->result();
        $attendanceMap = [];
        foreach ($attendance as $row) $attendanceMap[$row->subject_name][$row->attendance_status] = (int) $row->total;
        return ['enrollment' => $enrollment, 'grades' => $grades, 'attendance' => $attendanceMap];
    }

    public function save_promotion(array $data)
    {
        return $this->db->insert('student_promotion_history', $data);
    }

    public function get_promotions($schoolYear = '')
    {
        $this->db->select('p.*, e.student_id, e.grade_level, s.student_no, s.first_name, s.middle_name, s.last_name')
            ->from('student_promotion_history p')->join('student_enrollments e', 'e.id = p.enrollment_id')->join('students s', 's.id = e.student_id');
        if ($schoolYear !== '') $this->db->where('p.school_year', $schoolYear);
        return $this->db->order_by('p.school_year', 'DESC')->order_by('p.decision_date', 'DESC')->order_by('p.id', 'DESC')->order_by('s.last_name', 'ASC')->get()->result();
    }

    private function grade_code($grade)
    {
        $grade = trim((string) $grade);
        if (strcasecmp($grade, 'Nursery') === 0) return 'N';
        if (strcasecmp($grade, 'Kindergarten') === 0) return 'K';
        if (preg_match('/^Grade\s*(.+)$/i', $grade, $match)) return trim($match[1]);
        return $grade;
    }
}
