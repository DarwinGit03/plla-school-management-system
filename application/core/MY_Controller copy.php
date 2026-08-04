<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    /**
     * Logged-in user information
     */
    protected $current_user = NULL;

    /**
     * Shared data for views
     */
    protected $data = [];

    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | Load Common Models
        |--------------------------------------------------------------------------
        */

        $this->load->model('auth/User_model');

        /*
        |--------------------------------------------------------------------------
        | Load Common Libraries
        |--------------------------------------------------------------------------
        */

        $this->load->library('session');

        /*
        |--------------------------------------------------------------------------
        | Load Common Libraries
        |--------------------------------------------------------------------------
        */
        
        $this->load->library('Session_service');

        /*
        |--------------------------------------------------------------------------
        | Load Common Helpers
        |--------------------------------------------------------------------------
        */

        $this->load->helper(['url', 'auth']);

        /*
        |--------------------------------------------------------------------------
        | Get Current User
        |--------------------------------------------------------------------------
        */

        $this->loadCurrentUser();

        /*
        |--------------------------------------------------------------------------
        | Check Active Session
        |--------------------------------------------------------------------------
        */

        // if(
        //     $this->session->userdata('user_id')
        // )
        // {
        //     $this->checkSessionTimeout();
        // }
        // if($this->session->userdata('logged_in'))
        // {
        //     $this->checkSessionTimeout();
        // }
    }

    /*
    |--------------------------------------------------------------------------
    | Session Timeout
    |--------------------------------------------------------------------------
    */
    // private function checkSessionTimeout()
    // {
    //     if(!$this->session->userdata('user_id'))
    //     {
    //         return;
    //     }

    //     $user = $this->User_model->getById(
    //         $this->session->userdata('user_id')
    //     );

    //     if(
    //         !$user ||
    //         $user->session_token !=
    //         $this->session->userdata('session_token')
    //     )
    //     {
    //         $this->session_service->destroy();

    //         redirect('login');

    //         return;
    //     }

    //     // Continue with timeout checks...
    // }

    private function checkSessionTimeout()
    {
        $this->audit_log_service->log(
                $this->session->userdata('user_id'),
                'LOGS',
                'IN',
                'OKEY HERE'
            );
        if(!$this->session->userdata('logged_in'))
        {
            return;
        }

        $user = $this->User_model->getById(
            $this->session->userdata('user_id')
        );

        if(
            !$user ||
            $user->session_token !=
            $this->session->userdata('session_token')
        )
        {
            
            $this->audit_log_service->log(
                $this->session->userdata('user_id'),
                'core/My_Controller',
                'SESSION_TOKEN',
                'Automatic logout due to updated token.'
            );
            $this->session_service->destroy();

            redirect('login');

            return;
        }

        if($this->session_service->expired())
        {
            $this->audit_log_service->log(
                $this->session->userdata('user_id'),
                'core/My_Controller',
                'SESSION_TIMEOUT',
                'Automatic logout due to inactivity.'
            );

            $this->session_service->destroy();
            redirect('login');
            return;
        }

        $this->session_service->touch();
    }


    // private function checkSessionTimeout()
    // {
        
    //     if(!$this->session->userdata('user_id'))
    //     {
    //         return;
    //     }

    //     if(
    //         !$user ||
    //         $user->session_token !=
    //         $this->session->userdata('session_token')
    //     )
    //     {
    //         $this->session->sess_destroy();

    //         redirect('login');

    //         return;
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Check Session Timeout
    //     |--------------------------------------------------------------------------
    //     */


    //     if($this->session_service->expired())
    //     {
    //         $this->audit_log_service->log(

    //             $this->session->userdata('user_id'),
    //             'core/My_Controller',
    //             'SESSION_TIMEOUT',
    //             'Automatic logout due to inactivity.'

    //         );

    //         $this->session_service->destroy();

    //         redirect('login');
    //         return
    //     }

    //     $this->session_service->touch();
    // }

    

    /**
     * Get logged-in user
     */
    protected function loadCurrentUser()
    {
        $user_id =
            $this->session->userdata('user_id');

        if(!$user_id)
        {
            return;
        }
        
        $this->current_user = $this->User_model->getById($user_id);
        $this->data['current_user'] = $this->current_user;
    }
    /*
    |--------------------------------------------------------------------------
    | Require Login
    |--------------------------------------------------------------------------
    */

    protected function requireLogin()
    {
        require_login();
    }

    /*
    |--------------------------------------------------------------------------
    | Guest Only
    |--------------------------------------------------------------------------
    */

    protected function guestOnly()
    {
        guest_only();
    }

    /*
    |--------------------------------------------------------------------------
    | Require Role
    |--------------------------------------------------------------------------
    */

    protected function requireRole($roles)
    {
        require_role($roles);
    }

    /*
    |--------------------------------------------------------------------------
    | JSON Response
    |--------------------------------------------------------------------------
    */

    protected function jsonResponse($status, $message, $data = [])
    {
        $response = array_merge(
            [
                'status'  => $status,
                'message' => $message
            ],
            $data
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(
                json_encode($response)
            );
    }

    protected function checkSession()
    {
        if(!$this->session->userdata('user_id'))
        {
            redirect('login');
            return;
        }

        $user = $this->User_model->getById(
            $this->session->userdata('user_id')
        );

        if(
            !$user ||
            $user->session_token !=
            $this->session->userdata('session_token')
        )
        {
            $this->session_service->destroy();

            redirect('login');

            return;
        }

        if($this->session_service->expired())
        {
            $this->session_service->destroy();

            redirect('login');

            return;
        }

        $this->session_service->touch();
    }

}