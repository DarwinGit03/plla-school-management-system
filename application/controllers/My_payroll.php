<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_payroll extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole([3]);
        $this->load->model('teacher/teacher_model');
        $this->load->model('teacher/payroll_model');
    }

    public function index()
    {
        $teacher = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        $this->render_page([
            'title' => 'My pay history',
            'page_title' => 'My pay history',
            'page_subtitle' => 'Review your salary rate history and finalized payslips.',
            'breadcrumb' => ['Faculty', 'My pay history'],
            'teacher' => $teacher,
            'salary_history' => $teacher ? $this->teacher_model->get_salary_history($teacher->id) : [],
            'payroll_items' => $teacher ? $this->payroll_model->get_teacher_history($teacher->id, true) : []
        ]);
    }

    public function payslip($item_id)
    {
        $teacher = $this->teacher_model->get_by_user_id((int) $this->session->userdata('user_id'));
        $item = $this->payroll_model->get_item((int) $item_id);
        if (!$teacher || !$item || (int) $item->teacher_id !== (int) $teacher->id || $item->period_status !== 'finalized') {
            show_404();
            return;
        }

        $deductions = $this->payroll_model->get_deductions_for_items([(int) $item->id]);
        $this->load->library('teacher_payslip_pdf');
        $pdf = $this->teacher_payslip_pdf->generate($item, $deductions[$item->id] ?? []);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $item->employee_no_snapshot)
            . '_payslip_' . $item->period_start . '.pdf';

        return $this->output
            ->set_content_type('application/pdf')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_output($pdf);
    }

    private function render_page(array $data)
    {
        $this->load->view('dashboard/layouts/master', array_merge($data, [
            'content' => 'payroll/my_history'
        ]));
    }
}
