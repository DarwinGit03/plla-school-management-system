<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Session_service
{
    protected $CI;
    /**
        * Session timeout in seconds
        * Default = 30 minutes
    */
    protected $timeout = 1800;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /*
    |--------------------------------------------------------------------------
    | Update Activity
    |--------------------------------------------------------------------------
    */

    public function touch()
    {
        $this->CI->session->set_userdata([

            'last_activity' => time()

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Is Expired?
    |--------------------------------------------------------------------------
    */

    public function expired()
    {
        $security = $this->CI->config->item('security');

        $timeout = $security['session_timeout'];

        $last =
            $this->CI->session
                     ->userdata(
                        'last_activity'
                     );

        if(empty($last))
        {
            return false;
        }

        return (time() - $last) > $timeout;
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


}