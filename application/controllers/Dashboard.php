<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        // $this->checkSession();
    }

    public function index()
    {
        $this->load->view(
            'dashboard/index',
            $this->data
        );
    }
}