<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Session_service
{
    /**
     * CodeIgniter Instance
     */
    protected $CI;

    /**
     * Session timeout in seconds
     * Default = 30 minutes
     */
    protected $timeout = 1800;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->library('session');

        $this->CI->load->helper('url');

        $this->CI->load->model('auth/User_model');
    }

    /*
    |--------------------------------------------------------------------------
    | Create Login Session
    |--------------------------------------------------------------------------
    */

    public function createSession($user)
    {
        $token = bin2hex(
            // random_bytes(32)
            random_bytes($this->CI->config->item('session_token_length'))
        );

        $this->CI
             ->User_model
             ->updateSessionToken(
                    $user->id,
                    $token
             );

        $this->CI
             ->session
             ->set_userdata([

                'user_id'=>$user->id,

                'role_id'=>$user->role_id,

                'employee_no'=>$user->employee_no,

                'email'=>$user->email,

                'logged_in'=>true,

                'session_token'=>$token,

                'last_activity'=>time()

             ]);

        return $token;
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy Session
    |--------------------------------------------------------------------------
    */

    public function destroy()
    {
        $this->CI
             ->session
             ->sess_destroy();
    }

    /*
    |--------------------------------------------------------------------------
    | Is Logged In
    |--------------------------------------------------------------------------
    */

    public function isLoggedIn()
    {
        return $this->CI
                    ->session
                    ->userdata('logged_in') === true;
    }

    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    public function currentUser()
    {
        if(
            !$this->isLoggedIn()
        )
        {
            return NULL;
        }

        return $this->CI
                    ->User_model
                    ->getById(

                        $this->CI
                             ->session
                             ->userdata(
                                'user_id'
                             )

                    );
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Session Token
    |--------------------------------------------------------------------------
    */

    public function verifyToken()
    {
        if(
            !$this->isLoggedIn()
        )
        {
            return true;
        }

        $user =
            $this->currentUser();

        if(!$user)
        {
            return false;
        }

        return

            $user->session_token ==

            $this->CI
                 ->session
                 ->userdata(
                    'session_token'
                 );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Activity
    |--------------------------------------------------------------------------
    */

    public function touch()
    {
        $this->CI
             ->session
             ->set_userdata(

                'last_activity',

                time()

             );
    }

    /*
    |--------------------------------------------------------------------------
    | Session Expired
    |--------------------------------------------------------------------------
    */

    public function expired()
    {
        $last =

            $this->CI
                 ->session
                 ->userdata(
                    'last_activity'
                 );

        if(!$last)
        {
            return false;
        }

        return

            (time()-$last)

            >

            $this->CI->config->item('session_timeout'); //
    }

    /*
    |--------------------------------------------------------------------------
    | Force Logout
    |--------------------------------------------------------------------------
    */

    public function forceLogout($message = NULL)
    {
        $this->destroy();

        if($message)
        {
            $this->CI
                ->session
                ->set_flashdata(

                    'error',

                    $message

                );
        }

        redirect('login');
    }

}