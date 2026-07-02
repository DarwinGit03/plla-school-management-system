<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function index()
    {
        if (!$this->session->userdata('logged_in')) {

            redirect('login');
        }

        echo "<h1>Dashboard</h1>";

        echo "<hr>";

        echo "Welcome : ".
             $this->session->userdata('first_name');

        echo "<br>";

        echo "Role : ".
             $this->session->userdata('role_name');
    }
}