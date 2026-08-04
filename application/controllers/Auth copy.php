<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | Load Libraries
        |--------------------------------------------------------------------------
        */

        $this->load->library('auth_service');
    }

    /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $this->session->sess_destroy();

        $data['title']      = 'Login';
        $data['page_js']    = 'assets/js/auth/login.js';

        $this->load->view(
            'auth/login',$data
        );
    }
    
    public function forgot_password()
    {
        $this->session->sess_destroy();
        $data = [
            'title'     => 'Forgot Password', 
            'page_js'   =>  'assets/js/auth/forgot-password.js'
        ];

        // $data['title']      = 'Forgot Password';
        // $data['page_js']    = 'assets/js/auth/forgot-password.js';

        $this->load->view(
            'auth/forgot_password',$data
        );
    }

    public function verify_otp_page()
    {
        if($this->session->userdata('otp_completed'))
        {
            redirect('reset-password-page');
            return;
        }

        //OTP already completed. and Don't allow user to visit Verify OTP again.

        $email = $this->session->userdata('reset_email');
        if(!$email)
        {
            redirect('forgot-password');
            return;
        }

        $data = [
            'title'     => 'Verify OTP',
            'page_js'   => 'assets/js/auth/verify-otp.js',
            'page_css'  => 'assets/css/verify-otp.css',
            'email'     => $email
        ];

        $this->load->view(
            'auth/verify_otp',$data
        );
    }

    public function verify_otp()
    {
        $otp =
            $this->input
                ->post(
                    'otp'
                );

        $email =
            $this->session
                ->userdata(
                    'reset_email'
                );

        if(!$email)
        {
            echo json_encode([

                'status'=>false,

                'message'=>'Session expired.'

            ]);

            return;
        }

        $result =
            $this->auth_service
                ->verifyOTP(
                    $email,
                    $otp
                );

        echo json_encode($result);
    }

    // public function verify_otp()
    // {
    //     $email = $this->session->userdata('reset_email');
    //     if(!$email)
    //     {
    //         return $this->jsonResponse(false, 'Session expired.');
    //     }

    //     $otp = $this->input->post('otp');

    //     // $result = $this->CI->auth_service->verifyOTP($email, $otp);
    //     $result = $this->auth_service->verifyOTP($email, $otp);

    //     if(!$result['status'])
    //     {
    //         return $result;
    //     }

    //     $this->session->set_userdata([
    //         'reset_email'   => $email,
    //         'otp_verified'  => true,
    //         'otp_completed' => true
    //     ]);

    //     return [
    //         'status'  => true,
    //         'message' => 'OTP verified successfully.'
    //     ];
    // }
    
    public function reset_password_page()
    {
        if(!$this->session->userdata('otp_verified'))
        {
            redirect('verify-otp-page');
            return;
        }
        
        //OTP already completed. and Don't allow user to visit Verify OTP again.

        $email = $this->session->userdata('reset_email');
        if(!$email)
        {
            redirect('forgot-password');
            return;
        }

        $data = [
            'title'     => 'Verify OTP',
            'page_js'   => 'assets/js/auth/reset-password.js',
            'email'     => $email
        ];

        $this->load->view(
            'auth/reset_password',$data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save Password
    |--------------------------------------------------------------------------
    */

    public function save_password()
    {
        /*
        |--------------------------------------------------------------------------
        | Reset Session Validation
        |--------------------------------------------------------------------------
        */
        if(!$this->session->userdata('otp_verified'))
        {
            echo json_encode([
                'status'=>false,
                'message'=>'Unauthorized access.'
            ]);

            return;
        }

        $password = trim($this->input->post('password'));
        $confirm = trim($this->input->post('confirm_password'));
        $email = $this->session->userdata('reset_email');
        $result = $this->auth_service->savePassword($email, $password, $confirm);

        echo json_encode($result);
    }

    public function send_otp()
    {
        $email = trim($this->input->post('email'));

        if(empty($email))
        {
            return $this->jsonResponse(false, 'Email is required.');
        }

        $result = $this->auth_service->forgotPassword($email);

        return $this->jsonResponse(
            $result['status'],
            $result['message']
        );
    }

    public function resend_otp()
    {
        $email =
            $this->session
                ->userdata(
                    'reset_email'
                );

        if(!$email)
        {
            echo json_encode([

                'status' => false,

                'message' => 'Session expired.'

            ]);

            return;
        }

        $result =
            $this->auth_service
                ->resendOTP($email);

        echo json_encode($result);
    }

    public function login()
    {
        if(!$this->input->is_ajax_request())
        {
            show_404();
        }

        $email = trim($this->input->post('email'));
        $password =$this->input->post('password');

        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        if(empty($email) || empty($password))
        {
            echo json_encode([
                'status' => false,
                'message' => 'Email and password are required.'
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        $result = $this->auth_service->login($email, $password);
        echo json_encode($result);
    }

    // public function logout()
    // {
    //     $this->auth_service->logout();
    //     redirect('login');
    // }
    public function logout()
    {
        $this->audit_log_service
            ->log(
                $this->session->userdata('user_id'),
                'auth/logout',
                'LOGOUT',
                'User logged out.'

            );

        $this->session_service
            ->destroy();

        redirect('login');
    }

    // admin access 
    /*
    |--------------------------------------------------------------------------
    | Unlock User Account
    |--------------------------------------------------------------------------
    */

    public function unlockAccount($user_id)
    {
        /*
        |--------------------------------------------------------------------------
        | RBAC
        |--------------------------------------------------------------------------
        */

        if(
            !in_array(
                $this->session->userdata('role_id'),
                [1,2] // Administrator & Principal
            )
        )
        {
            show_error(
                'Unauthorized',
                403
            );
        }

        $result =
            $this->auth_service
                ->unlockAccount(
                    $user_id
                );
        echo json_encode($result);
    }
    
    public function keepAlive()
    {
        $this->session_service->touch();

        echo json_encode([
            'status' => true
        ]);
    }
}