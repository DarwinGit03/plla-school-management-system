<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Auth_model');
    }

    public function index()
    {
        $this->load->view('auth/login');
    }

    public function login()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $username = trim($this->input->post('username'));
        $password = trim($this->input->post('password'));

        if (empty($username) || empty($password)) {

            echo json_encode([
                'status' => false,
                'message' => 'Please fill all fields.'
            ]);

            return;
        }

        $user = $this->Auth_model->get_user($username);

        if (!$user) {

            echo json_encode([
                'status' => false,
                'message' => 'User not found.'
            ]);

            return;
        }

        if (!$user->is_active) {

            echo json_encode([
                'status' => false,
                'message' => 'Account disabled.'
            ]);

            return;
        }

        if (!password_verify($password, $user->password)) {

            echo json_encode([
                'status' => false,
                'message' => 'Invalid password.'
            ]);

            return;
        }

        $session = [

            'user_id'      => $user->id,
            'username'     => $user->username,
            'email'        => $user->email,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'role_id'      => $user->role_id,
            'role_name'    => $user->role_name,
            'logged_in'    => true

        ];

        $this->session->set_userdata($session);

        echo json_encode([
            'status' => true,
            'message' => 'Login successful.',
            'redirect' => base_url('dashboard')
        ]);
    }

    public function logout()
    {
        $this->session->sess_destroy();

        redirect('login');
    }
}