<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    protected $current_user = NULL;

    /*
    |--------------------------------------------------------------------------
    | Shared View Data
    |--------------------------------------------------------------------------
    */

    protected $data = [];

    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | Models
        |--------------------------------------------------------------------------
        */

        $this->load->model('auth/User_model');

        /*
        |--------------------------------------------------------------------------
        | Libraries
        |--------------------------------------------------------------------------
        */

        $this->load->library('session');

        $this->load->library('Session_service');

        $this->load->library('Audit_log_service');

        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        $this->load->helper([
            'url',
            'auth'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load Current User
        |--------------------------------------------------------------------------
        */

        $this->loadCurrentUser();
    }

    /*
    |--------------------------------------------------------------------------
    | Require Login
    |--------------------------------------------------------------------------
    */

    protected function requireLogin()
    {
        if(!$this->session_service->isLoggedIn())
        {
            redirect('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Single Session
        |--------------------------------------------------------------------------
        */

        if(!$this->session_service->verifyToken())
        {
            $this->audit_log_service->log(
                'auth',
                'FORCE_LOGOUT',
                null,
                'Logged out because another session replaced this login.'
            );

            $this->session_service->forceLogout(
                'You have been logged out because another session has replaced yours.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Session Timeout
        |--------------------------------------------------------------------------
        */

        if ($this->session_service->expired())
        {
            $sessionId =
                $this->session
                    ->userdata('session_token');


            /*
            |--------------------------------------------------------------------------
            | Close Login Log
            |--------------------------------------------------------------------------
            */

            if ($sessionId)
            {
                $logoutUpdated =
                    $this->Login_log_model
                        ->updateLogout(
                            $sessionId
                        );

                /*
                |--------------------------------------------------------------------------
                | Only Create Audit Log Once
                |--------------------------------------------------------------------------
                */

                if ($logoutUpdated)
                {
                    $this->audit_log_service->log(
                        'auth',
                        'SESSION_TIMEOUT',
                        null,
                        'Automatic logout because of inactivity.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Force Logout
            |--------------------------------------------------------------------------
            */

            $this->session_service->forceLogout(
                'Your session expired because of inactivity.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Activity
        |--------------------------------------------------------------------------
        */

        $this->session_service->touch();
    }

    /*
    |--------------------------------------------------------------------------
    | Guest Only
    |--------------------------------------------------------------------------
    */

    protected function guestOnly()
    {
        if($this->session_service->isLoggedIn())
        {
            redirect('dashboard');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Role Middleware
    |--------------------------------------------------------------------------
    */

    protected function requireRole($roles)
    {
        require_role($roles);
    }

    /*
    |--------------------------------------------------------------------------
    | Load Current User
    |--------------------------------------------------------------------------
    */

    protected function loadCurrentUser()
    {
        if(!$this->session_service->isLoggedIn())
        {
            return;
        }

        $this->current_user =
            $this->session_service->currentUser();

        $this->data['current_user'] =
            $this->current_user;
    }

    /*
    |--------------------------------------------------------------------------
    | JSON Response
    |--------------------------------------------------------------------------
    */

    protected function jsonResponse(
        $status,
        $message,
        $data = []
    )
    {
        $response = array_merge(
            [
                'status'  => $status,
                'message' => $message
            ],
            $data
        );

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(
                        json_encode($response)
                    );
    }

    protected function requireOTPVerification()
    {
        if(

            !$this->session
                ->userdata(
                        'otp_verified'
                )

        )
        {
            redirect('forgot-password');
        }
    }
}