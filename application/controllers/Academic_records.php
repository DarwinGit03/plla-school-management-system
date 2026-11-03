<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Academic_records extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->library('form_validation');
        $this->load->model('academic/academic_records_model');
        $this->load->model('academic/student_subject_grade_model');
        $this->load->model('academic/subject_model');
        $this->load->model('finance/fee_configuration_model');
        $this->load->model('teacher/teacher_model');
    }

    public function review()
    {
        $this->requireAnyPermission(['academic.attendance.review', 'academic.grades.review']);
        $this->render_page('academic/review', [
            'title' => 'Academic review', 'page_title' => 'Academic review',
            'page_subtitle' => 'Review submitted grades and teacher attendance correction requests.',
            'breadcrumb' => ['Academic', 'Review'],
            'grade_queue' => $this->can('academic.grades.review') ? $this->student_subject_grade_model->get_review_queue() : [],
            'grade_corrections' => $this->can('academic.grades.review') ? $this->student_subject_grade_model->get_correction_queue('grade') : [],
            'attendance_queue' => $this->can('academic.attendance.review') ? $this->academic_records_model->get_correction_queue('attendance') : [],
            'correction_context' => function ($request) { return $this->academic_records_model->get_correction_context($request); },
            'can_review_grades' => $this->can('academic.grades.review'),
            'can_review_attendance' => $this->can('academic.attendance.review'),
            'success_message' => $this->session->flashdata('success'),
            'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function grade_decision()
    {
        $this->requirePermission('academic.grades.review');
        if ($this->input->method() !== 'post') { show_404(); return; }
        $id = (int) $this->input->post('grade_id', true);
        $decision = trim((string) $this->input->post('decision', true));
        $note = trim((string) $this->input->post('review_note', true));
        $grade = $this->student_subject_grade_model->get_grade($id);
        if (!$grade || !in_array($decision, ['approved', 'returned'], true) || strlen($note) > 500 || ($decision === 'returned' && strlen($note) < 5)) {
            $this->session->set_flashdata('error', 'Choose a valid review decision. A returned grade needs a reason.');
            return redirect('academic/review');
        }
        if ($decision === 'approved' && $grade->final_grade === null) {
            $this->session->set_flashdata('error', 'A grade set needs a final grade before it can be approved for report cards.');
            return redirect('academic/review');
        }
        $context = $this->academic_records_model->get_correction_context((object) ['record_type' => 'grade', 'record_id' => $id]);
        if (!$this->student_subject_grade_model->decide_review($id, (int) $this->session->userdata('user_id'), $decision, $note)) {
            $this->session->set_flashdata('error', 'This grade may have already been reviewed. Refresh the queue and try again.');
            return redirect('academic/review');
        }
        $name = $context ? trim($context->first_name . ' ' . $context->last_name) : 'the student';
        $this->audit_log_service->log('Academic grades', strtoupper($decision), $id,
            ucfirst($decision) . ' grades for ' . $name . ' in ' . ($context->subject_name ?? 'a subject') . '.' . ($note !== '' ? ' Reviewer note: ' . $note : ''),
            ['grade_id' => $id, 'decision' => $decision, 'review_note' => $note]);
        $this->session->set_flashdata('success', $decision === 'approved' ? 'Grades approved and available for report cards.' : 'Grades returned to the teacher for correction.');
        return redirect('academic/review');
    }

    public function correction_decision()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $requestId = (int) $this->input->post('request_id', true);
        $request = $this->student_subject_grade_model->get_correction_request($requestId);
        if ($request && $request->status !== 'pending') $request = null;
        if (!$request) { $this->session->set_flashdata('error', 'This correction request is no longer pending.'); return redirect('academic/review'); }
        $this->requirePermission($request->record_type === 'grade' ? 'academic.grades.review' : 'academic.attendance.review');
        $decision = trim((string) $this->input->post('decision', true));
        $note = trim((string) $this->input->post('review_note', true));
        if (!in_array($decision, ['approved', 'rejected'], true) || strlen($note) > 500 || ($decision === 'rejected' && strlen($note) < 5)) {
            $this->session->set_flashdata('error', 'Choose a valid decision. Rejected corrections need a reason.');
            return redirect('academic/review');
        }
        $context = $this->academic_records_model->get_correction_context($request);
        $reviewerId = (int) $this->session->userdata('user_id');
        $result = $request->record_type === 'grade'
            ? $this->student_subject_grade_model->decide_grade_correction($requestId, $reviewerId, $decision === 'approved', $note)
            : $this->academic_records_model->decide_attendance_correction($requestId, $reviewerId, $decision === 'approved', $note);
        if (!$result) { $this->session->set_flashdata('error', 'The correction could not be reviewed. Refresh the queue and try again.'); return redirect('academic/review'); }
        $detail = $request->record_type === 'grade'
            ? 'grade correction for ' . trim(($context->first_name ?? '') . ' ' . ($context->last_name ?? '')) . ' in ' . ($context->subject_name ?? 'a subject')
            : 'attendance correction for ' . trim(($context->first_name ?? '') . ' ' . ($context->last_name ?? '')) . ' in ' . ($context->subject_name ?? 'a subject') . ' on ' . ($context->attendance_date ?? '');
        $this->audit_log_service->log('Academic ' . ($request->record_type === 'grade' ? 'grades' : 'attendance'), 'CORRECTION_' . strtoupper($decision), $requestId,
            ucfirst($decision) . ' ' . $detail . '. Request reason: ' . $request->reason . ($note !== '' ? ' Reviewer note: ' . $note : ''),
            ['request_id' => $requestId, 'record_type' => $request->record_type, 'record_id' => (int) $request->record_id, 'proposed_values' => json_decode($request->proposed_values, true), 'request_reason' => $request->reason, 'review_note' => $note]);
        $this->session->set_flashdata('success', 'Correction request ' . $decision . '.');
        return redirect('academic/review');
    }

    public function report_cards()
    {
        $this->requirePermission('academic.report_cards.view');
        $years = array_map(function ($row) { return (string) $row->school_year; }, $this->fee_configuration_model->get_school_years());
        $year = $this->resolve_school_year_filter($years, 'school_year');
        $search = trim((string) $this->input->get('q', true));
        $this->render_page('academic/report_cards', [
            'title' => 'Report cards', 'page_title' => 'Student report cards',
            'page_subtitle' => 'Review approved subject grades and attendance by school year.',
            'breadcrumb' => ['Academic', 'Report cards'], 'years' => $years, 'school_year' => $year,
            'search' => $search, 'enrollments' => $year !== '' ? $this->academic_records_model->get_enrollments($year, $search) : []
        ]);
    }

    public function report_card($enrollmentId = 0, $print = false)
    {
        $this->requirePermission('academic.report_cards.view');
        $card = $this->academic_records_model->get_report_card((int) $enrollmentId);
        if (!$card) { show_404(); return; }
        $data = [
            'title' => 'Report card', 'page_title' => 'Student report card',
            'page_subtitle' => 'School year ' . $card['enrollment']->academic_year,
            'breadcrumb' => ['Academic', 'Report cards', 'Student report card'],
            'card' => $card, 'print_mode' => (bool) $print,
            'grade_label' => $this->fee_configuration_model->grade_label($card['enrollment']->grade_level)
        ];
        if ($print) {
            $this->load->view('academic/report_card_print', $data);
            return;
        }
        $this->render_page('academic/report_card', $data);
    }

    public function promotions()
    {
        $this->requirePermission('academic.promotion.manage');
        $years = array_map(function ($row) { return (string) $row->school_year; }, $this->fee_configuration_model->get_school_years());
        $year = $this->resolve_school_year_filter($years, 'school_year');
        $this->render_page('academic/promotions', [
            'title' => 'Promotion history', 'page_title' => 'Year-end decisions',
            'page_subtitle' => 'Record promoted, retained, or transferred decisions without creating next-year enrollments.',
            'breadcrumb' => ['Academic', 'Promotion history'], 'years' => $years, 'school_year' => $year,
            'enrollments' => $year !== '' ? $this->academic_records_model->get_enrollments($year) : [],
            'promotion_history' => $this->academic_records_model->get_promotions($year),
            'grades' => $year !== '' ? $this->fee_configuration_model->get_grade_codes_by_year($year) : [],
            'success_message' => $this->session->flashdata('success'), 'error_message' => $this->session->flashdata('error')
        ]);
    }

    public function save_promotion()
    {
        $this->requirePermission('academic.promotion.manage');
        if ($this->input->method() !== 'post') { show_404(); return; }
        $enrollmentId = (int) $this->input->post('enrollment_id', true);
        $enrollment = $this->db->where('id', $enrollmentId)->where_in('status', ['active', 'completed'])->get('student_enrollments')->row();
        $decision = trim((string) $this->input->post('decision', true));
        $date = trim((string) $this->input->post('decision_date', true));
        $nextGrade = trim((string) $this->input->post('next_grade_code', true));
        $notes = trim((string) $this->input->post('notes', true));
        if (!$enrollment || !in_array($decision, ['promoted', 'retained', 'transferred'], true) || !$this->valid_date($date) || strlen($notes) > 500) {
            $this->session->set_flashdata('error', 'Enter a valid student, year-end decision, date, and optional note.');
            return redirect('academic/promotions');
        }
        if (strlen($nextGrade) > 30 || ($decision === 'promoted' && $nextGrade === '')) {
            $this->session->set_flashdata('error', 'Enter the next grade for a promoted student.');
            return redirect('academic/promotions?' . http_build_query(['school_year' => $enrollment->academic_year]));
        }
        if ($decision !== 'promoted') $nextGrade = null;
        $data = [
            'enrollment_id' => $enrollmentId, 'school_year' => $enrollment->academic_year,
            'decision' => $decision, 'next_grade_code' => $decision === 'promoted' ? $nextGrade : null,
            'decision_date' => $date, 'notes' => $notes !== '' ? $notes : null,
            'decided_by' => (int) $this->session->userdata('user_id')
        ];
        if (!$this->academic_records_model->save_promotion($data)) {
            $this->session->set_flashdata('error', 'The year-end decision could not be saved.');
            return redirect('academic/promotions?' . http_build_query(['school_year' => $enrollment->academic_year]));
        }
        $student = $this->db->where('id', (int) $enrollment->student_id)->get('students')->row();
        $name = $student ? trim($student->first_name . ' ' . $student->last_name) : 'student enrollment #' . $enrollmentId;
        $this->audit_log_service->log('Academic', 'PROMOTION_DECISION_RECORDED', $enrollmentId,
            'Recorded ' . $decision . ' decision for ' . $name . ' for school year ' . $enrollment->academic_year . '.',
            ['enrollment_id' => $enrollmentId, 'school_year' => $enrollment->academic_year, 'decision' => $decision, 'next_grade_code' => $data['next_grade_code'], 'decision_date' => $date, 'notes' => $data['notes']]);
        $this->session->set_flashdata('success', 'Year-end decision saved. No new enrollment was created.');
        return redirect('academic/promotions?' . http_build_query(['school_year' => $enrollment->academic_year]));
    }

    private function valid_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    private function render_page($content, array $data)
    {
        $this->load->view('dashboard/layouts/master', array_merge($this->data, $data, ['content' => $content]));
    }
}
