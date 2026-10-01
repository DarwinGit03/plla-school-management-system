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
        $this->load->model('finance/fee_configuration_model');
    }

    public function index()
    {
        $employee = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        $assignments = [];

        if ($employee && $employee->status === 'active') {
            $assignments = $this->subject_model->get_assignments([
                'employee_id' => (int) $employee->id,
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
            'assignments' => $assignments
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

        if (!$this->student_subject_grade_model->save_for_enrollment(
            (int) $assignment->id,
            $enrollmentId,
            $grades,
            (int) $this->session->userdata('user_id')
        )) {
            $this->session->set_flashdata('error', 'The student grades could not be saved. Please try again.');
            return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
        }

        $this->audit_log_service->log('academic_grades', 'STUDENT_SUBJECT_GRADES_SAVED', $enrollmentId, 'Teacher saved grades for an assigned subject.');
        $this->session->set_flashdata('success', 'Grades saved.');
        return redirect('my-classes/' . (int) $assignmentId . '#student-' . $enrollmentId);
    }
}
