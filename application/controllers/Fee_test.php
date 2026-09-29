<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Fee_test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('finance/Fee_configuration_model');
    }


    public function index()
    {
        $school_year = '2026-2027';

        $grade_code = '3';

        $payment_mode = 'semi_annual';


        $data['fees'] =
            $this->Fee_configuration_model->get_complete_fees(
                $school_year,
                $grade_code,
                $payment_mode
            );


        $this->load->view(
            'finance/fee_test',
            $data
        );
    }
}