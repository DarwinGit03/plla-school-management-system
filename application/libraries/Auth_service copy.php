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
        | Load Dependencies
        |--------------------------------------------------------------------------
        */
        $this->CI->load->model(
            'auth/Login_log_model'
        );

        $this->CI->load->model(
            'auth/User_model'
        );

        $this->CI->load->library(
            'session'
        );
        $this->CI->load->library(
            'Audit_log_service', 
            'audit_log_service'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Login
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
                'message' => 'Invalid email or password.'
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

            if($user->failed_attempts>=5)
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
        | Generate Session Token
        |--------------------------------------------------------------------------
        */

        //test
        $sessionToken = bin2hex(
            // random_bytes(32)
            random_bytes($this->CI->config->item('session_token_length'))
        );

        $this->CI
            ->User_model
            ->updateSessionToken(
                $user->id,
                $sessionToken

        );
        /*
        |--------------------------------------------------------------------------
        | Create Session
        |--------------------------------------------------------------------------
        */

        // $this->createSession(
        //     $user
        // );
        
        
        $this->CI->session->set_userdata([
            'user_id'   => $user->id,
            'role_id'   => $user->role_id,
            'email'     => $user->email,
            'logged_in' => true,
            'session_token' => $sessionToken //test
        ]);

        /*
        |--------------------------------------------------------------------------
        | Start Session Activity Timer
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->session_service
            ->touch();

        
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
        $user_id =
            $this->CI
                ->session
                ->userdata(
                    'user_id'
                );

        $this->CI
            ->Login_log_model
            ->updateLogout(
                $user_id
            );

        $this->CI
            ->session
            ->sess_destroy();
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
        | Check if user exists
        |--------------------------------------------------------------------------
        */

        $user = $this->CI->User_model->getByEmail($email);

        if (!$user)
        {
            return [
                'status'  => false,
                'message' => 'Email address not found.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Generate OTP
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

        if (!$mail['status'])
        {
            return [
                'status'  => false,
                'message' => $mail['message']
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Store Reset Session
        | User has requested a password reset.
        | OTP has NOT been verified yet.
        |--------------------------------------------------------------------------
        */

        $this->CI->session->set_userdata([
            'reset_email' => $email,
            'otp_verified' => false
        ]);

        return [
            'status'  => true,
            'message' => 'OTP has been sent to your email.'
        ];
    }

    // public function verifyOTP($email, $otp)
    // {
    //     $result = $this->CI->otp_service->verifyOTP($email, $otp);

    //     if(!$result['status'])
    //     {
    //         return $result;
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Allow Reset Password
    //     |--------------------------------------------------------------------------
    //     */

    //     $this->CI->session->set_userdata([
    //         'otp_verified' => true, 
    //         'reset_email' => $email,
    //         'otp_completed' => true
    //     ]);

    //     return [
    //         'status'=>true,
    //         'message'=>'OTP verified successfully.'

    //     ];
    // }

    public function verifyOTP($email,$otp)
    {
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
        | OTP Verified
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->session
            ->set_userdata([

                'reset_email'=>$email,

                'otp_verified'=>true,

                'otp_completed'=>true

            ]);
        return [

            'status'=>true,

            'message'=>'OTP verified successfully.'

        ];
    }

    public function resendOTP($email)
    {
        return $this->CI->otp_service->resendOTP($email);
    }

    public function savePassword($email, $password, $confirm)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Email
        |--------------------------------------------------------------------------
        */

        if(empty($email))
        {
            return [
                'status'    => false,
                'message'   => 'Session expired.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Password Match
        |--------------------------------------------------------------------------
        */

        if($password !== $confirm)
        {
            return [
                'status'    => false,
                'message'   => 'Passwords do not match.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Password
        |--------------------------------------------------------------------------
        */

        if(strlen($password) < $this->CI->config->item('password_min_length'))
        {
            return [
                'status'    => false,
                'message'   => 'Password must be at least 8 characters.'
            ];
        }

        if(!preg_match('/[A-Z]/', $password))
        {
            return [
                'status'    => false,
                'message'   => 'Password must contain an uppercase letter.'
            ];
        }

        if(!preg_match('/[a-z]/', $password))
        {
            return [
                'status'    => false,
                'message'   => 'Password must contain a lowercase letter.'
            ];
        }

        if(!preg_match('/[0-9]/', $password))
        {
            return [
                'status'    => false,
                'message'   => 'Password must contain a number.'
            ];
        }

        if(!preg_match('/[\W]/', $password))
        {
            return [
                'status'    => false,
                'message'   => 'Password must contain a special character.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Get User
        |--------------------------------------------------------------------------
        */

        $user = $this->CI->User_model->getByEmail($email);
                // $this->CI->User_model->getByEmail($email);

        if(!$user)
        {
            return [
                'status'    => false,
                'message'   => 'User not found.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Hash Password
        |--------------------------------------------------------------------------
        */

        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Update password
        $updated = $this->CI->User_model->updatePassword($email, $hash, $password);

        if(!$updated['status'])
        {
            return $updated;
        }

        /*
        |--------------------------------------------------------------------------
        | Invalidate Existing Sessions
        |--------------------------------------------------------------------------
        */

        $newToken = bin2hex( random_bytes($this->CI->config->item('session_token_length')));
        //test
        $this->CI->User_model->updateSessionToken(
            $user->id,
            $newToken
        );
        
        // Delete OTP
        $this->CI->Password_reset_model->deleteByEmail($email);

        // Audit Log
        $this->CI->audit_log_service->log(
            $user->id,
            'auth/reset_password',
            'PASSWORD_RESET',
            'User successfully change their password.'
        );

        // Destroy Session
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