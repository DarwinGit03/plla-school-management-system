<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RBAC
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /*
    |--------------------------------------------------------------------------
    | Check Login
    |--------------------------------------------------------------------------
    */

    public function isLoggedIn()
    {
        if (!$this->CI->session->userdata('logged_in'))
        {
            redirect('login');
            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Check Roles
    |--------------------------------------------------------------------------
    */

    public function allow($roles = [])
    {
        $this->isLoggedIn();

        $user_role =
            $this->CI->session->userdata('role_id');

        if (!in_array($user_role, $roles))
        {
            show_error(
                'Access Denied',
                403,
                'Unauthorized'
            );
        }
    }
}