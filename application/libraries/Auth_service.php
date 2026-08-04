<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    /**
        * ✓ Login
        * ✓ Logout
        * ✓ Create Session
        * ✓ Destroy Session
        * ✓ Check Authentication
        * ✓ Get Current User
    */

class Auth_service
{
    /**
     * CodeIgniter Instance
     */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        /*
        |--------------------------------------------------------------------------
        | Models
        |--------------------------------------------------------------------------
        */

        $this->CI->load->model('auth/User_model');
        $this->CI->load->model('Password_reset_model');
        $this->CI->load->model(
            'auth/Login_log_model'
        );

        /*
        |--------------------------------------------------------------------------
        | Libraries
        |--------------------------------------------------------------------------
        */

        $this->CI->load->library('Session_service');
        $this->CI->load->library('Otp_service');
        $this->CI->load->library('Mail_service');
        $this->CI->load->library('Audit_log_service');

        /*
        |--------------------------------------------------------------------------
        | Config
        |--------------------------------------------------------------------------
        */

        $this->CI->load->config('auth');
        
        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        $this->CI->load->helper([
            'url',
            'security'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    * Authenticate user credentials.
    * @param string $email
    * @param string $password
    * @return array
    |--------------------------------------------------------------------------
    */

    public function login($email, $password)
    {
        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = $this->CI->User_model->getByEmail($email);

        /*
        |--------------------------------------------------------------------------
        | User Not Found
        |--------------------------------------------------------------------------
        */

        if(!$user)
        {
            return [
                'status' => false,
                'message' => $this->CI->config
                      ->item('msg_invalid_login')
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Auto Unlock Expired Lock
        |--------------------------------------------------------------------------
        */

        if($this->CI->User_model->lockExpired($user))
        {
            $this->CI
                ->User_model
                ->unlockAccount(
                    $user->id
                );

            $this->CI
                ->audit_log_service
                ->log(

                    $user->id,
                    'auth_service/login',
                    'ACCOUNT_UNLOCKED',
                    'Lock expired automatically.'

                );

            /*
            |--------------------------------------------------------------------------
            | Reload Updated User
            |--------------------------------------------------------------------------
            */

            $user = $this->CI
                        ->User_model
                        ->getById(
                            $user->id
                        );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Account Lock
        |--------------------------------------------------------------------------
        */

        if($this->CI->User_model->isLocked($user))
        {
            return [
                'status'=>false,
                'message'=>
                    'Account locked. Try again in '
                    .
                    $this->CI
                        ->User_model
                        ->remainingLockMinutes(
                            $user
                        )
                    .
                    ' minute(s).'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Check Account Status
        |--------------------------------------------------------------------------
        */

        if($user->is_active == 0)
        {
            return [
                'status' => false,
                'message' => 'Account is inactive.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        if(!password_verify($password,$user->password))
        {

            /*
            |--------------------------------------------------------------------------
            | Reset Attempts if Expired
            |--------------------------------------------------------------------------
            */

            if(

                $this->CI
                    ->User_model
                    ->shouldResetFailedAttempts(
                        $user
                    )

            )
            {
                $this->CI
                    ->User_model
                    ->resetFailedAttempts(

                        $user->id

                    );

                $user =

                    $this->CI
                        ->User_model
                        ->getById(

                            $user->id

                        );
            }

            /*
            |--------------------------------------------------------------------------
            | Increase Attempts
            |--------------------------------------------------------------------------
            */

            $this->CI
                ->User_model
                ->increaseFailedAttempts(
                    $user->id
                );

            /*
            |--------------------------------------------------------------------------
            | Refresh User
            |--------------------------------------------------------------------------
            */

            $user =
                $this->CI
                    ->User_model
                    ->getById(
                        $user->id
                    );

            /*
            |--------------------------------------------------------------------------
            | Lock? //Account locked. Try again in 15 minute(s).
            |--------------------------------------------------------------------------
            */

            // if($user->failed_attempts>=5)
            if($user->failed_attempts >= $this->CI->config->item('login_max_attempts'))
            {
                $this->CI
                    ->User_model
                    ->lockAccount(
                        $user->id
                    );
                /*
                |--------------------------------------------------------------------------
                | Audit Log
                |--------------------------------------------------------------------------
                */

                $this->CI
                    ->audit_log_service
                    ->log(
                        $user->id,
                        'auth/login',
                        'ACCOUNT_LOCKED',
                        'Too many failed login attempts.'
                    );

                return [
                    'status'=>false,
                    'message'=>
                        'Account locked for 5 minutes.'
                ];
            }

            $this->logLogin(
                $user,
                'FAILED'
            );
            return [
                'status' => false,
                'message' => 'Invalid password.',
                'remaining' =>
                    5 - $user->failed_attempts
            ];
        }




        /*
        |--------------------------------------------------------------------------
        | Reset Failed Attempts
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->User_model
            ->resetFailedAttempts(
                $user->id
            );

        /*  
        |--------------------------------------------------------------------------
        | Create Login Session
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->session_service
            ->create($user);

        /*
        |--------------------------------------------------------------------------
        | Create Session
        |--------------------------------------------------------------------------
        */
        
        // $this->CI->session->set_userdata([
        //     'user_id'   => $user->id,
        //     'role_id'   => $user->role_id,
        //     'email'     => $user->email,
        //     'logged_in' => true,
        //     'session_token' => $sessionToken //test
        // ]);

        /*
        |--------------------------------------------------------------------------
        | Start Session Activity Timer
        |--------------------------------------------------------------------------
        // */

        // $this->CI
        //     ->session_service
        //     ->touch();

        
        /*
        |--------------------------------------------------------------------------
        | Update Last Login
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->User_model
            ->updateLastLogin(
                $user->id
            );


        /*
        |--------------------------------------------------------------------------
        | Log Successful Login
        |--------------------------------------------------------------------------
        */

        $this->logLogin(
            $user, 'SUCCESS'
        );

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return [
            'status' => true,
            'message' => 'Login successful.',
            'redirect' => 'dashboard'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create Session
    |--------------------------------------------------------------------------
    */

    private function createSession(
        $user
    )
    {
        $this->CI
             ->session
             ->set_userdata([

                'user_id' =>
                    $user->id,

                'role_id' =>
                    $user->role_id,

                'email' =>
                    $user->email,

                'full_name' =>
                    trim(
                        $user->first_name
                        .' '.
                        $user->last_name
                    ),

                'logged_in' =>
                    true

             ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout()
    {
        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $userId =
            $this->CI
                ->session
                ->userdata(
                    'user_id'
                );

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        if($userId)
        {
            $this->CI
                ->audit_log_service
                ->log(

                    $userId,

                    'auth/logout',

                    'LOGOUT',

                    'User logged out.'

                );
        }

        /*
        |--------------------------------------------------------------------------
        | Destroy Session
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->session_service
            ->destroy();

        return [

            'status' => true,

            'message' => 'Logout successful.'

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Check Login
    |--------------------------------------------------------------------------
    */

    public function check()
    {
        return
            $this->CI
                 ->session
                 ->userdata(
                     'logged_in'
                 ) === true;
    }

    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        $id =
            $this->CI
                 ->session
                 ->userdata(
                     'user_id'
                 );

        if(!$id)
        {
            return null;
        }

        return
            $this->CI
                 ->User_model
                 ->getById(
                     $id
                 );
    }

    /*
    |--------------------------------------------------------------------------
    | Log Login
    |--------------------------------------------------------------------------
    */

    private function logLogin($user, $status)
    {
        $this->CI->Login_log_model->create([
            'user_id'     => $user->id,
            'ip_address'  => $this->CI->input->ip_address(),
            'browser'     => $this->CI->input->user_agent(),
            'status'      => $status,
            'login_time'  => date('Y-m-d H:i:s')

        ]);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    */

    public function forgotPassword($email)
    {
        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = $this->CI->User_model->getByEmail($email);

        if(!$user)
        {
            return [
                'status' => false,
                'message' => 'Email address not found.'

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create OTP
        |--------------------------------------------------------------------------
        */

        $otp = $this->CI->otp_service->createOTP($email);

        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        $mail = $this->CI->mail_service->sendOTP(
            $email,
            $otp
        );

        if(!$mail['status'])
        {
            return [

                'status'=>false,

                'message'=>$mail['message']

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Store Reset Email
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->session
            ->set_userdata([
                'reset_email'=>$email,
                'otp_verified' => false
            ]);

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->audit_log_service
            ->log(
                $user->id,
                'auth/forgot-password',
                'PASSWORD_RESET_REQUEST',
                'User requested password reset.'

            );

        return [

            'status'=>true,

            'message'=>'OTP has been sent to your email.'

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Verify OTP
    |--------------------------------------------------------------------------
    */

    public function verifyOTP($email, $otp)
    {
        /*
        |--------------------------------------------------------------------------
        | Verify OTP
        |--------------------------------------------------------------------------
        */

        $result =

            $this->CI
                ->otp_service
                ->verifyOTP(
                    $email,
                    $otp
                );

        if(!$result['status'])
        {
            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user =

            $this->CI
                ->User_model
                ->getByEmail(
                    $email
                );

        /*
        |--------------------------------------------------------------------------
        | Allow Password Reset
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->session
            ->set_userdata([

                'otp_verified' => true,

                'otp_completed' => true,

                'reset_email' => $email

            ]);

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        if($user)
        {
            $this->CI
                ->audit_log_service
                ->log(

                    $user->id,

                    'auth/verify-otp',

                    'OTP_VERIFIED',

                    'User successfully verified OTP.'

                );
        }

        return [

            'status'=>true,

            'message'=>'OTP verified successfully.'

        ];
    }

    public function resendOTP($email)
    {
        return $this->CI->otp_service->resendOTP($email);
    }

    /*
    |--------------------------------------------------------------------------
    | Save New Password
    |--------------------------------------------------------------------------
    */

    public function savePassword($password)
    {
        /*
        |--------------------------------------------------------------------------
        | Verify Reset Session
        |--------------------------------------------------------------------------
        */

        $email = $this->CI->session->userdata('reset_email');

        if(!$email)
        {
            return [
                'status'  => false,
                'message' => 'Reset session expired.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = $this->CI->User_model->getByEmail($email);

        $this->CI->db->trans_begin(); //update

        if(!$user)
        {
            return [
                'status'  => false,
                'message' => 'User not found.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Encrypt Password
        |--------------------------------------------------------------------------
        */

        $hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        $this->CI->User_model->updatePassword(
            $user->id,
            $hash
        );

        /*
        |--------------------------------------------------------------------------
        | Generate New Session Token
        |--------------------------------------------------------------------------
        */

        $token = bin2hex(
            // random_bytes(32)
            random_bytes($this->CI->config->item('session_token_length'))
        );

        $this->CI->User_model->updateSessionToken(
            $user->id,
            $token
        );

        /*
        |--------------------------------------------------------------------------
        | Remove Password Reset Record
        |--------------------------------------------------------------------------
        */

        $this->CI->Password_reset_model->delete(
            $email
        );

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        $this->CI->audit_log_service->log(

            $user->id,

            'auth/reset-password',

            'PASSWORD_RESET',

            'User successfully reset password.'

        );

        /*
        |--------------------------------------------------------------------------
        | Commit / Rollback
        |--------------------------------------------------------------------------
        */

        if($this->CI->db->trans_status() === FALSE)
        {
            $this->CI->db->trans_rollback();
            return [
                'status' => false,
                'message' => 'Unable to update password.'
            ];
        }
        $this->CI->db->trans_commit();

        /*
        |--------------------------------------------------------------------------
        | Clear Reset Session
        |--------------------------------------------------------------------------
        */

        $this->CI->session->unset_userdata([
            'reset_email',
            'otp_verified',
            'otp_completed'
        ]);

        return [
            'status' => true,
            'message' => 'Password changed successfully.'
        ];
    }

    // admin access 
    /*
    |--------------------------------------------------------------------------
    | Unlock Account
    |--------------------------------------------------------------------------
    */

    public function unlockAccount($user_id)
    {
        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = $this->CI->user->getById($user_id);

        if(!$user)
        {
            return [
                'status'=>false,
                'message'=>'User not found.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Unlock
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->user
            ->unlockAccount(
                $user_id
            );

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        $admin_id =
            $this->CI
                ->session
                ->userdata(
                    'user_id'
                );

        $this->CI
            ->audit_log_service
            ->log(
                $admin_id,
                'auth_service/unlockAccount',
                'ACCOUNT_UNLOCKED',
                'Unlocked account: '.$user->email

            );

        return [

            'status'=>true,

            'message'=>'Account unlocked successfully.'

        ];
    }

}