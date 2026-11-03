<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_classes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requirePermission('academic.subjects.view_own');
        $this->load->model('teacher/teacher_model');
        $this->load->model('academic/subject_model');
        $this->load->model('academic/section_assignment_model');
        $this->load->model('academic/student_subject_grade_model');
        $this->load->model('academic/academic_records_model');
        $this->load->model('finance/fee_configuration_model');
    }

    public function index()
    {
        $employee = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        $years = array_map(function ($row) { return (string) $row->school_year; }, $this->fee_configuration_model->get_school_years());
        $selectedYear = $this->resolve_school_year_filter($years, 'school_year');
        $assignments = [];

        if ($employee && $employee->status === 'active') {
            $assignments = $this->subject_model->get_assignments([
                'employee_id' => (int) $employee->id,
                'school_year' => $selectedYear,
                'status' => 'active'
            ]);

            foreach ($assignments as $assignment) {
                $assignment->grade_label = $this->fee_configuration_model->grade_label($assignment->grade_code);
            }
        }

        $this->load->view('dashboard/layouts/master', array_merge($this->data, [
            'title' => 'My classes',
            'page_title' => 'My classes',
            'page_subtitle' => 'Your subjects and class lists by school year and grade.',
            'breadcrumb' => ['Dashboard', 'My classes'],
            'content' => 'dashboard/teacher/my_classes',
            'employee' => $employee,
            'assignments' => $assignments,
            'school_years' => $years,
            'selected_school_year' => $selectedYear
        ]));
    }

    public function roster($assignmentId = 0)
    {
        $employee = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        if (!$employee || $employee->status !== 'active') { show_404(); return; }

        $assignment = $this->subject_model->get_assignment_for_employee((int) $assignmentId, (int) $employee->id);
        if (!$assignment) { show_404(); return; }

        $students = $this->section_assignment_model->get_students_for_assignment(
            $assignment->offering_school_year,
            $assignment->offering_grade_code
        );
        $gradesByEnrollment = $this->student_subject_grade_model->get_for_assignment((int) $assignment->id);

        $this->load->view('dashboard/layouts/master', array_merge($this->data, [
            'title' => 'Class list',
            'page_title' => 'My class list',
            'page_subtitle' => 'Students actively enrolled in this subject offering.',
            'breadcrumb' => ['Dashboard', 'My classes', 'Class list'],
            'content' => 'dashboard/teacher/class_roster',
            'assignment' => $assignment,
            'grade_label' => $this->fee_configuration_model->grade_label($assignment->offering_grade_code),
            'students' => $students,
            'grades_by_enrollment' => $gradesByEnrollment,
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]));
    }

    public function save_grade($assignmentId = 0)
    {
        if ($this->input->method() !== 'post') { show_404(); return; }

        $employee = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        if (!$employee || $employee->status !== 'active') { show_404(); return; }

        $assignment = $this->subject_model->get_assignment_for_employee((int) $assignmentId, (int) $employee->id);
        if (!$assignment) { show_404(); return; }

        $enrollmentId = (int) $this->input->post('enrollment_id');
        if (!$this->section_assignment_model->is_active_enrollment_for_assignment(
            $enrollmentId,
            $assignment->offering_school_year,
            $assignment->offering_grade_code
        )) {
            $this->session->set_flashdata('error', 'This student is no longer actively enrolled in this class. Refresh the roster and try again.');
            return redirect('my-classes/' . (int) $assignmentId);
        }

        $grades = [];
        foreach (['prelim_grade', 'midterm_grade', 'finals_grade', 'final_grade'] as $field) {
            $value = trim((string) $this->input->post($field));
            if ($value === '') {
                $grades[$field] = null;
                continue;
            }
            if (!is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                $this->session->set_flashdata('error', 'Grades must be numbers from 0 to 100.');
                return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
            }
            $grades[$field] = number_format((float) $value, 2, '.', '');
        }

        $currentGrades = $this->student_subject_grade_model->get_for_assignment((int) $assignment->id);
        $current = $currentGrades[$enrollmentId] ?? null;
        if ($current && $current->review_status === 'approved') {
            $reason = trim((string) $this->input->post('correction_reason', true));
            if (strlen($reason) < 5 || strlen($reason) > 500) {
                $this->session->set_flashdata('error', 'An approved grade correction needs a reason between 5 and 500 characters.');
                return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
            }
            $result = $this->student_subject_grade_model->request_correction((int) $current->id, $grades, $reason, (int) $this->session->userdata('user_id'));
            if (!$result['ok']) {
                $this->session->set_flashdata('error', 'The correction request could not be saved. Check whether another request is already pending.');
                return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
            }
            $this->audit_log_service->log('Academic', 'GRADE_CORRECTION_REQUESTED', (int) $current->id,
                'Requested a grade correction for enrollment #' . $enrollmentId . ' in "' . $assignment->subject_name . '": ' . $reason,
                ['assignment_id' => (int) $assignment->id, 'enrollment_id' => $enrollmentId, 'reason' => $reason, 'proposed_grades' => $grades]);
            $this->session->set_flashdata('success', 'Grade correction submitted for academic review.');
            return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
        }

        $result = $this->student_subject_grade_model->save_for_enrollment(
            (int) $assignment->id, $enrollmentId, $grades, (int) $this->session->userdata('user_id')
        );
        if (empty($result['ok'])) {
            $message = ($result['code'] ?? '') === 'approved_locked'
                ? 'Approved grades are locked. Submit a correction request with a reason.'
                : 'The student grades could not be submitted. Please try again.';
            $this->session->set_flashdata('error', $message);
            return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
        }

        $this->audit_log_service->log(
            'Academic',
            'GRADES_UPDATED',
            $enrollmentId,
            'Submitted grades for academic review for student enrollment #' . $enrollmentId . ' in "' . $assignment->subject_name . '", Grade ' . $assignment->offering_grade_code . ' (' . $assignment->offering_school_year . ').',
            ['assignment_id' => (int) $assignment->id, 'student_enrollment_id' => $enrollmentId, 'subject_name' => $assignment->subject_name, 'school_year' => $assignment->offering_school_year, 'grade' => $assignment->offering_grade_code, 'grades' => $grades]
        );
        $this->session->set_flashdata('success', 'Grades submitted for academic review.');
        return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
    }

    public function attendance($assignmentId = 0)
    {
        $employee = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        if (!$employee || $employee->status !== 'active') { show_404(); return; }
        $assignment = $this->subject_model->get_assignment_for_employee((int) $assignmentId, (int) $employee->id);
        if (!$assignment) { show_404(); return; }
        $sections = $this->academic_records_model->get_sections_for_assignment($assignment);
        $sectionId = (int) $this->input->get('section_id', true);
        $validSections = array_map(function ($section) { return (int) $section->id; }, $sections);
        if (!in_array($sectionId, $validSections, true)) $sectionId = $validSections ? $validSections[0] : 0;
        $date = trim((string) $this->input->get('date', true));
        if (!$this->valid_attendance_date($date)) $date = date('Y-m-d');
        $students = $sectionId ? $this->academic_records_model->get_students_for_section($assignment, $sectionId) : [];
        $session = $sectionId ? $this->academic_records_model->get_attendance_session($assignmentId, $sectionId, $date) : null;
        $attendance = $session ? $this->academic_records_model->get_attendance_for_session((int) $session->id) : [];
        $this->load->view('dashboard/layouts/master', array_merge($this->data, [
            'title' => 'Subject attendance', 'page_title' => 'Subject attendance',
            'page_subtitle' => 'Record attendance for students in your assigned subject and section.',
            'breadcrumb' => ['Academic', 'My classes', 'Attendance'],
            'content' => 'dashboard/teacher/attendance', 'assignment' => $assignment,
            'sections' => $sections, 'selected_section_id' => $sectionId, 'attendance_date' => $date,
            'students' => $students, 'session' => $session, 'attendance' => $attendance,
            'success_message' => $this->session->flashdata('success'), 'error_message' => $this->session->flashdata('error')
        ]));
    }

    public function save_attendance($assignmentId = 0)
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $employee = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        if (!$employee || $employee->status !== 'active') { show_404(); return; }
        $assignment = $this->subject_model->get_assignment_for_employee((int) $assignmentId, (int) $employee->id);
        if (!$assignment) { show_404(); return; }
        $sectionId = (int) $this->input->post('section_id', true);
        $date = trim((string) $this->input->post('attendance_date', true));
        $sectionIds = array_map(function ($section) { return (int) $section->id; }, $this->academic_records_model->get_sections_for_assignment($assignment));
        if (!in_array($sectionId, $sectionIds, true) || !$this->valid_attendance_date($date) || $date > date('Y-m-d')) {
            $this->session->set_flashdata('error', 'Choose a valid assigned section and attendance date no later than today.');
            return redirect('my-classes/' . (int) $assignmentId . '/attendance');
        }
        $students = $this->academic_records_model->get_students_for_section($assignment, $sectionId);
        $posted = $this->input->post('attendance');
        if (!is_array($posted) || count($posted) !== count($students)) {
            $this->session->set_flashdata('error', 'Submit attendance for every active student in this section.');
            return redirect('my-classes/' . (int) $assignmentId . '/attendance?' . http_build_query(['section_id' => $sectionId, 'date' => $date]));
        }
        $statuses = [];
        foreach ($students as $student) {
            $key = (string) $student->enrollment_id;
            $status = isset($posted[$key]) ? trim((string) $posted[$key]) : '';
            if (!in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
                $this->session->set_flashdata('error', 'Choose a valid attendance status for every student.');
                return redirect('my-classes/' . (int) $assignmentId . '/attendance?' . http_build_query(['section_id' => $sectionId, 'date' => $date]));
            }
            $statuses[(int) $student->enrollment_id] = $status;
        }
        $session = $this->academic_records_model->get_attendance_session($assignmentId, $sectionId, $date);
        $actorId = (int) $this->session->userdata('user_id');
        if (!$session) {
            $result = $this->academic_records_model->save_initial_attendance($assignmentId, $sectionId, $date, $statuses, $actorId);
            if (empty($result['ok'])) {
                $this->session->set_flashdata('error', 'Attendance session already exists or could not be saved. Refresh and review it.');
                return redirect('my-classes/' . (int) $assignmentId . '/attendance?' . http_build_query(['section_id' => $sectionId, 'date' => $date]));
            }
            $this->audit_log_service->log('Academic attendance', 'ATTENDANCE_RECORDED', (int) $result['session_id'],
                'Recorded attendance for ' . count($statuses) . ' students.',
                ['assignment_id' => (int) $assignmentId, 'section_id' => $sectionId, 'date' => $date, 'student_count' => count($statuses)]);
            $this->session->set_flashdata('success', 'Attendance recorded. Later changes require academic reviewer approval.');
        } else {
            $records = $this->academic_records_model->get_attendance_for_session((int) $session->id);
            $reason = trim((string) $this->input->post('correction_reason', true));
            $changes = [];
            foreach ($statuses as $enrollmentId => $status) {
                $record = $records[$enrollmentId] ?? null;
                if (!$record) continue;
                if ($record->attendance_status !== $status) $changes[] = [$record, $status];
            }
            if ($changes && (strlen($reason) < 5 || strlen($reason) > 500)) {
                $this->session->set_flashdata('error', 'Attendance changes require a reason between 5 and 500 characters.');
                return redirect('my-classes/' . (int) $assignmentId . '/attendance?' . http_build_query(['section_id' => $sectionId, 'date' => $date]));
            }
            $correctionChanges = array_map(function ($change) { return ['record' => $change[0], 'status' => $change[1]]; }, $changes);
            $result = $this->academic_records_model->request_attendance_corrections($correctionChanges, $reason, $actorId);
            if (empty($result['ok'])) {
                $this->session->set_flashdata('error', 'Attendance corrections could not be submitted. At least one may already have a pending request.');
                return redirect('my-classes/' . (int) $assignmentId . '/attendance?' . http_build_query(['section_id' => $sectionId, 'date' => $date]));
            }
            foreach ($changes as $change) {
                $this->audit_log_service->log('Academic attendance', 'ATTENDANCE_CORRECTION_REQUESTED', (int) $change[0]->id,
                    'Requested attendance correction from ' . $change[0]->attendance_status . ' to ' . $change[1] . ' for ' . $assignment->subject_name . ' on ' . $date . '. Reason: ' . $reason,
                    ['attendance_id' => (int) $change[0]->id, 'from' => $change[0]->attendance_status, 'to' => $change[1], 'reason' => $reason]);
            }
            $this->session->set_flashdata('success', $changes ? 'Attendance corrections submitted for review.' : 'Attendance is already up to date.');
        }
        return redirect('my-classes/' . (int) $assignmentId . '/attendance?' . http_build_query(['section_id' => $sectionId, 'date' => $date]));
    }

    private function valid_attendance_date($date)
    {
        $parsed = DateTime::createFromFormat('!Y-m-d', (string) $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
