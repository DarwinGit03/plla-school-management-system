<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        $this->load->model('Dashboard_model');
    }

    public function index()
    {
        $role = $this->session->userdata('role_id');

        switch($role)
        {
            /*
            |--------------------------------------------------------------------------
            | Administrator
            |--------------------------------------------------------------------------
            */

            // case 1:
            //     $data['title']      = 'Administrator Dashboard';
            //     $data['page_css']    = 'assets/css/dashboard.css';
            //     $data['page_js']    = 'assets/js/auth/login.js';

            //     $this->load->view(
            //         'dashboard/admin/dashboard', $data
            //     );

            // break;

            case 1:
                $this->data['title'] = 'Administrator Dashboard';

                $this->data['page_title'] = 'Administrator Dashboard';

                $this->data['page_subtitle'] = 'School Management System';

                $this->data['breadcrumb'] = [
                    'Dashboard'
                ];

                $this->data['content'] = 'dashboard/admin/dashboard';

                $this->load->view(
                    'dashboard/layouts/master',
                    $this->data
                );

            break;

            /*
            |--------------------------------------------------------------------------
            | Principal
            |--------------------------------------------------------------------------
            */

            case 2:

                echo "Teacher Dashboard";

            break;

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */

            case 3:

                echo "Finance Dashboard";

            break;

            default:

                show_404();

        }

    }

}