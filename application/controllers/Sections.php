<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sections extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole([1, 2]);

        $this->load->library('form_validation');
        $this->load->model('academic/academic_section_model');
        $this->load->model('finance/fee_configuration_model');
    }

    public function index()
    {
        $years = $this->school_year_values();
        $requestedYear = trim((string) $this->input->get('year', true));
        $year = in_array($requestedYear, $years, true) ? $requestedYear : '';
        $gradeOptions = $this->grade_options_by_year();

        $requestedGrade = trim((string) $this->input->get('grade', true));
        $gradeValues = $year !== '' ? ($gradeOptions[$year] ?? []) : [];
        if ($year === '') {
            foreach ($gradeOptions as $yearGrades) {
                $gradeValues = array_merge($gradeValues, $yearGrades);
            }
            $gradeValues = array_values(array_unique($gradeValues));
        }
        $grade = in_array($requestedGrade, $gradeValues, true) ? $requestedGrade : '';

        $requestedStatus = trim((string) $this->input->get('status', true));
        $status = in_array($requestedStatus, ['Active', 'Inactive'], true) ? $requestedStatus : '';
        $search = trim((string) $this->input->get('q', true));

        $this->load->view('dashboard/layouts/master', [
            'title' => 'Academic Sections',
            'page_title' => 'Academic Sections',
            'page_subtitle' => 'Manage the sections available for each configured school year and grade.',
            'breadcrumb' => ['Academic', 'Sections'],
            'content' => 'academic/sections/index',
            'page_scripts' => ['assets/js/academic/sections.js'],
            'sections' => $this->academic_section_model->get_sections([
                'year' => $year,
                'grade' => $grade,
                'status' => $status,
                'search' => $search
            ]),
            'school_years' => $years,
            'grade_options_by_year' => $gradeOptions,
            'selected_year' => $year,
            'selected_grade' => $grade,
            'selected_status' => $status,
            'search' => $search,
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error'),
            'validation_error' => $this->session->flashdata('validation_error')
        ]);
    }

    public function grades()
    {
        $year = trim((string) $this->input->get('year', true));
        if (!in_array($year, $this->school_year_values(), true)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['grades' => []]));
        }

        $grades = array_map(function ($row) {
            return (string) $row->grade;
        }, $this->fee_configuration_model->get_grade_codes_by_year($year));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['grades' => $grades]));
    }

    public function save()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $id = (int) $this->input->post('id', true);
        $existing = $id > 0 ? $this->academic_section_model->get_by_id($id) : null;
        if ($id > 0 && !$existing) {
            show_404();
            return;
        }

        $this->form_validation->set_rules('year', 'School year', 'trim|required|max_length[20]');
        $this->form_validation->set_rules('grade', 'Grade', 'trim|required|max_length[30]');
        $this->form_validation->set_rules('section', 'Section name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|in_list[Active,Inactive]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', 'Please correct the highlighted section details.');
            $this->session->set_flashdata('validation_error', strip_tags(validation_errors(' ', ' ')));
            return redirect('academic/sections');
        }

        $year = trim((string) $this->input->post('year', true));
        $grade = trim((string) $this->input->post('grade', true));
        $section = trim((string) $this->input->post('section', true));
        $status = trim((string) $this->input->post('status', true));
        $grades = $this->grade_options_by_year();

        if (!in_array($year, $this->school_year_values(), true)
            || !in_array($grade, $grades[$year] ?? [], true)) {
            $this->session->set_flashdata('error', 'Choose a school year and grade available in Fee Configurations.');
            return redirect('academic/sections');
        }

        if ($this->academic_section_model->exists_duplicate($year, $grade, $section, $id)) {
            $this->session->set_flashdata('error', 'That section already exists for this school year and grade.');
            return redirect('academic/sections');
        }

        $employeeNo = (string) $this->session->userdata('employee_no');
        $data = [
            'year' => $year,
            'grade' => $grade,
            'section' => $section,
            'status' => $status,
            'created_by' => $employeeNo,
            'updated_by' => $employeeNo
        ];
        $saved = $id > 0
            ? $this->academic_section_model->update_section($id, $data)
            : $this->academic_section_model->create_section($data);

        if (!$saved) {
            $this->session->set_flashdata('error', 'The section could not be saved. Please try again.');
            return redirect('academic/sections');
        }

        $recordId = $id > 0 ? $id : (int) $saved;
        $this->audit_log_service->log(
            'academic_sections',
            $id > 0 ? 'SECTION_UPDATED' : 'SECTION_CREATED',
            $recordId,
            $id > 0 ? 'Academic section updated.' : 'Academic section created.',
            $data
        );

        $this->session->set_flashdata('success', $id > 0 ? 'Section updated.' : 'Section created.');
        return redirect('academic/sections?year=' . rawurlencode($year));
    }

    public function change_status()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $id = (int) $this->input->post('id', true);
        $status = trim((string) $this->input->post('status', true));
        $record = $id > 0 ? $this->academic_section_model->get_by_id($id) : null;
        if (!$record || !in_array($status, ['Active', 'Inactive'], true)) {
            show_404();
            return;
        }

        if (!$this->academic_section_model->update_status(
            $id,
            $status,
            (string) $this->session->userdata('employee_no')
        )) {
            $this->session->set_flashdata('error', 'The section status could not be updated.');
        } else {
            $this->audit_log_service->log(
                'academic_sections',
                'SECTION_STATUS_CHANGED',
                $id,
                'Academic section status changed.',
                ['from' => $record->status, 'to' => $status]
            );
            $this->session->set_flashdata('success', 'Section status updated to ' . $status . '.');
        }

        return redirect('academic/sections?year=' . rawurlencode($record->year));
    }

    private function school_year_values()
    {
        return array_map(function ($row) {
            return (string) $row->school_year;
        }, $this->fee_configuration_model->get_school_years());
    }

    private function grade_options_by_year()
    {
        $options = [];
        foreach ($this->school_year_values() as $year) {
            $options[$year] = array_map(function ($row) {
                return (string) $row->grade;
            }, $this->fee_configuration_model->get_grade_codes_by_year($year));
        }

        return $options;
    }
}
