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
            | Administrator /principal
            |--------------------------------------------------------------------------
            */

            case 1:
            case 2:
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

            case 3:

                echo "Teacher Dashboard";

            break;

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */

            case 4:

                echo "Finance Dashboard";

            break;

            default:

                show_404();

        }

    }

}