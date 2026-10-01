<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Section_assignments extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requirePermission('students.section.assign');

        $this->load->model('academic/section_assignment_model');
        $this->load->model('finance/fee_configuration_model');
    }

    public function index()
    {
        $years = array_map(function ($row) {
            return (string) $row->school_year;
        }, $this->fee_configuration_model->get_school_years());

        $year = trim((string) $this->input->get('year', true));
        if (!in_array($year, $years, true)) $year = '';

        $grades = $year !== ''
            ? array_map(function ($row) {
                return (string) $row->grade;
            }, $this->fee_configuration_model->get_grade_codes_by_year($year))
            : [];

        $grade = trim((string) $this->input->get('grade', true));
        if (!in_array($grade, $grades, true)) $grade = '';

        $sections = ($year !== '' && $grade !== '')
            ? $this->section_assignment_model->get_active_sections($year, $grade)
            : [];

        $sectionId = (int) $this->input->get('section_id', true);
        $validSectionIds = array_map(function ($section) {
            return (int) $section->id;
        }, $sections);
        if (!in_array($sectionId, $validSectionIds, true)) $sectionId = 0;

        $search = trim((string) $this->input->get('q', true));
        $students = ($year !== '' && $grade !== '')
            ? $this->section_assignment_model->get_students_for_assignment($year, $grade, $search)
            : [];
        $this->load->view('dashboard/layouts/master', [
            'title' => 'Section Assignment',
            'page_title' => 'Section Assignment',
            'page_subtitle' => 'Assign or update active student section placements by school year and grade.',
            'breadcrumb' => ['Students', 'Section Assignment'],
            'content' => 'students/section_assignments/index',
            'page_scripts' => ['assets/js/student/section-assignments.js'],
            'school_years' => $years,
            'grades' => $grades,
            'sections' => $sections,
            'selected_year' => $year,
            'selected_grade' => $grade,
            'selected_section_id' => $sectionId,
            'search' => $search,
            'students' => $students,
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function assign()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $sectionId = (int) $this->input->post('section_id', true);
        $section = $sectionId > 0 ? $this->section_assignment_model->get_section($sectionId) : null;
        $ids = $this->input->post('enrollment_ids');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        }))) : [];

        if (!$section || $section->status !== 'Active' || !$ids) {
            $this->session->set_flashdata('error', 'Choose an active section and at least one active student.');
            return $this->redirect_to_filters();
        }

        $result = $this->section_assignment_model->assign_enrollments(
            $ids,
            $section,
            (string) $this->session->userdata('employee_no')
        );

        if (!$result['ok']) {
            $message = $result['code'] === 'selection_changed'
                ? 'One or more selected enrollments are no longer eligible. Refresh the list and try again.'
                : 'The selected students could not be assigned. Please try again.';
            $this->session->set_flashdata('error', $message);
            return $this->redirect_to_filters();
        }

        $this->audit_log_service->log(
            'student_enrollments',
            'SECTION_ASSIGNED',
            $sectionId,
            count($ids) . ' student enrollment(s) assigned or moved to section ' . $section->section . '.',
            ['section_id' => $sectionId, 'enrollment_ids' => $ids]
        );

        $this->session->set_flashdata('success', count($ids) . ' student(s) assigned or moved to ' . $section->section . '.');
        return $this->redirect_to_filters();
    }

    private function redirect_to_filters()
    {
        $filters = [
            'year' => trim((string) $this->input->post('year', true)),
            'grade' => trim((string) $this->input->post('grade', true)),
            'section_id' => (int) $this->input->post('section_id', true),
            'q' => trim((string) $this->input->post('q', true))
        ];
        return redirect('students/section-assignments?' . http_build_query($filters));
    }
}
