<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('system/Audit_log_model');
        $this->CI->load->library('session');
    }

    /*
    |--------------------------------------------------------------------------
    | Log Activity
    |--------------------------------------------------------------------------
    */
    
    public function log($module, $action, $description)
    {
        $this->CI->Audit_log_model->create([
            'user_id' =>$this->CI->session->userdata('user_id'),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->CI->input->ip_address()
        ]);
    }
}