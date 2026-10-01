<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subjects extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requirePermission('academic.subjects.manage');
        $this->load->library('form_validation');
        $this->load->model('academic/subject_model');
        $this->load->model('finance/fee_configuration_model');
        $this->load->model('teacher/teacher_model');
    }

    public function index()
    {
        $years = $this->school_year_values();
        $gradeMap = $this->grade_options_by_year();
        $gradeLabels = [];
        foreach ($gradeMap as $gradeCodes) {
            foreach ($gradeCodes as $gradeCode) $gradeLabels[$gradeCode] = $this->fee_configuration_model->grade_label($gradeCode);
        }
        $teacherFilter = (int) $this->input->get('teacher', true);
        $subjectFilter = (int) $this->input->get('subject', true);
        $editId = (int) $this->input->get('edit', true);
        $editSubject = $editId > 0 ? $this->subject_model->get_subject($editId) : null;

        $this->load->view('dashboard/layouts/master', [
            'title' => 'Subjects and teacher assignments',
            'page_title' => 'Subjects and teacher assignments',
            'page_subtitle' => 'Create a subject for a school year and grade, then assign its teacher.',
            'breadcrumb' => ['Academic', 'Subjects and Assignments'],
            'content' => 'academic/subjects/index',
            'page_scripts' => ['assets/js/academic/subjects.js'],
            'years' => $years,
            'grade_options_by_year' => $gradeMap,
            'grade_labels' => $gradeLabels,
            'edit_subject' => $editSubject,
            'subjects' => $this->subject_model->get_subjects(),
            'active_subjects' => $this->subject_model->get_subjects(['is_active' => 1, 'unassigned_only' => 1, 'scoped_only' => 1]),
            'employees' => $this->teacher_model->get_teachers(['status' => 'active']),
            'teachers' => $this->teacher_model->get_teachers(),
            'teacher_filter' => $teacherFilter,
            'subject_filter' => $subjectFilter,
            'assignments' => $this->subject_model->get_assignments([
                'employee_id' => $teacherFilter,
                'subject_id' => $subjectFilter
            ]),
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function save_subject()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $id = (int) $this->input->post('id', true);
        $existing = $id > 0 ? $this->subject_model->get_subject($id) : null;
        if ($id > 0 && !$existing) { show_404(); return; }

        $this->form_validation->set_rules('subject_name', 'Subject name', 'trim|required|max_length[120]');
        $this->form_validation->set_rules('subject_code', 'Subject code', 'trim|alpha_dash|max_length[30]');
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[500]');
        $this->form_validation->set_rules('school_year', 'School year', 'trim|required|max_length[20]');
        $this->form_validation->set_rules('grade_code', 'Grade', 'trim|required|max_length[30]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', trim(strip_tags(validation_errors(' ', ' '))));
            return redirect('academic/subjects#subject-catalog');
        }

        $year = trim((string) $this->input->post('school_year', true));
        $grade = trim((string) $this->input->post('grade_code', true));
        $gradeOptions = $this->grade_options_by_year();
        if (!in_array($year, $this->school_year_values(), true)
            || !in_array($grade, $gradeOptions[$year] ?? [], true)) {
            $this->session->set_flashdata('error', 'Choose a valid school year and grade from the current fee configurations.');
            return redirect('academic/subjects#subject-catalog');
        }

        $actor = (string) ($this->session->userdata('employee_no') ?: $this->session->userdata('user_id'));
        $result = $this->subject_model->save_subject([
            'school_year' => $year,
            'grade_code' => $grade,
            'subject_name' => trim((string) $this->input->post('subject_name', true)),
            'subject_code' => strtoupper(trim((string) $this->input->post('subject_code', true))),
            'description' => trim((string) $this->input->post('description', true)),
            'updated_by' => $actor
        ], $id);

        if (!$result['ok']) {
            $messages = [
                'duplicate_name' => 'A subject with that name already exists for this school year and grade.',
                'duplicate_code' => 'That subject code is already in use for this school year and grade.',
                'assigned_scope_locked' => 'School year and grade cannot be changed after a teacher assignment has been recorded.',
                'save_failed' => 'The subject could not be saved. Please try again.'
            ];
            $this->session->set_flashdata('error', $messages[$result['code']] ?? 'The subject could not be saved.');
            return redirect('academic/subjects#subject-catalog');
        }

        $this->audit_log_service->log('academic_subjects', $id > 0 ? 'SUBJECT_UPDATED' : 'SUBJECT_CREATED', $result['id'], 'Academic subject saved.', [
            'school_year' => $year,
            'grade_code' => $grade,
            'subject_name' => $this->input->post('subject_name', true),
            'subject_code' => $this->input->post('subject_code', true)
        ]);
        $this->session->set_flashdata('success', $id > 0 ? 'Subject updated.' : 'Subject added to the catalog.');
        return redirect('academic/subjects#subject-catalog');
    }

    public function subject_status()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $id = (int) $this->input->post('id', true);
        $active = (int) $this->input->post('is_active', true);
        $subject = $this->subject_model->get_subject($id);
        if (!$subject || !in_array($active, [0, 1], true)) { show_404(); return; }

        $actor = (string) ($this->session->userdata('employee_no') ?: $this->session->userdata('user_id'));
        if (!$this->subject_model->update_subject_status($id, $active, $actor)) {
            $this->session->set_flashdata('error', 'The subject status could not be updated.');
        } else {
            $this->audit_log_service->log('academic_subjects', 'SUBJECT_STATUS_CHANGED', $id, 'Academic subject status changed.', ['is_active' => $active]);
            $this->session->set_flashdata('success', $active ? 'Subject activated.' : 'Subject deactivated.');
        }
        return redirect('academic/subjects#subject-catalog');
    }

    public function assign()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required|integer');
        $this->form_validation->set_rules('employee_id', 'Teacher', 'trim|required|integer');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', trim(strip_tags(validation_errors(' ', ' '))));
            return redirect('academic/subjects#assignments');
        }

        $subjectId = (int) $this->input->post('subject_id', true);
        $employeeId = (int) $this->input->post('employee_id', true);
        $subject = $this->subject_model->get_subject($subjectId);
        $employee = $this->teacher_model->get_by_id($employeeId);
        $grades = $this->grade_options_by_year();

        if (!$subject || !(int) $subject->is_active
            || !in_array((string) $subject->school_year, $this->school_year_values(), true)
            || !in_array((string) $subject->grade_code, $grades[$subject->school_year] ?? [], true)
            || !$employee || $employee->status !== 'active') {
            $this->session->set_flashdata('error', 'Choose an active subject offering and an active teacher.');
            return redirect('academic/subjects#assignments');
        }

        $actor = (string) ($this->session->userdata('employee_no') ?: $this->session->userdata('user_id'));
        $result = $this->subject_model->assign_teacher([
            'school_year' => $subject->school_year,
            'grade_code' => $subject->grade_code,
            'subject_id' => $subjectId,
            'employee_id' => $employeeId,
            'assigned_by' => $actor
        ]);
        if (!$result['ok']) {
            $messages = [
                'already_assigned' => 'This subject offering already has a teacher. Remove that assignment before assigning another teacher.',
                'save_failed' => 'The teacher assignment could not be saved. Please try again.'
            ];
            $this->session->set_flashdata('error', $messages[$result['code']] ?? 'The teacher assignment could not be saved.');
            return redirect('academic/subjects#assignments');
        }

        $this->audit_log_service->log('employee_subject_assignments', 'TEACHER_SUBJECT_ASSIGNED', $result['id'], 'Subject assigned to an employee for a school year and grade.', [
            'employee_id' => $employeeId,
            'subject_id' => $subjectId,
            'school_year' => $subject->school_year,
            'grade_code' => $subject->grade_code
        ]);
        $this->session->set_flashdata('success', 'Subject assigned to the teacher.');
        return redirect('academic/subjects#assignments');
    }

    public function assignment_status()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $id = (int) $this->input->post('id', true);
        $status = trim((string) $this->input->post('status', true));
        $assignment = $this->subject_model->get_assignment($id);
        if (!$assignment || !in_array($status, ['active', 'inactive'], true)) { show_404(); return; }

        if (!$this->subject_model->update_assignment_status($id, $status)) {
            $this->session->set_flashdata('error', 'The teacher assignment could not be updated.');
        } else {
            $this->audit_log_service->log('employee_subject_assignments', 'ASSIGNMENT_STATUS_CHANGED', $id, 'Teacher subject assignment status changed.', ['status' => $status]);
            $this->session->set_flashdata('success', $status === 'active' ? 'Assignment reactivated.' : 'Assignment removed from the active list.');
        }
        return redirect('academic/subjects#assignments');
    }

    private function school_year_values()
    {
        return array_map(function ($row) { return (string) $row->school_year; }, $this->fee_configuration_model->get_school_years());
    }

    private function grade_options_by_year()
    {
        $options = [];
        foreach ($this->school_year_values() as $year) {
            $options[$year] = array_map(function ($row) { return (string) $row->grade; }, $this->fee_configuration_model->get_grade_codes_by_year($year));
        }
        return $options;
    }
}
