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
        $this->CI->load->model('auth/Password_reset_model');
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
        $this->CI->load->library('Password_validator');

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

        $user = $this->CI->User_model->getByLoginIdentity($email);

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
                'FAILED',
                null
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
            ->createSession($user);

            
        $sessionId = $this->CI
            ->session
            ->userdata(
                'session_token'
            );
        
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
            $user, 'SUCCESS', $sessionId
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

    // private function createSession(
    //     $user
    // )
    // {
    //     $this->CI
    //          ->session
    //          ->set_userdata([

    //             'user_id' =>
    //                 $user->id,

    //             'role_id' =>
    //                 $user->role_id,

    //             'email' =>
    //                 $user->email,

    //             'full_name' =>
    //                 trim(
    //                     $user->first_name
    //                     .' '.
    //                     $user->last_name
    //                 ),

    //             'logged_in' =>
    //                 true

    //         ]);

    // }

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

        $sessionId =
            $this->CI
                ->session
                ->userdata(
                    'session_token'
                );


        /*
        |--------------------------------------------------------------------------
        | Update Login Log
        |--------------------------------------------------------------------------
        */

        if($sessionId)
        {
            $this->CI
                ->Login_log_model
                ->updateLogout(
                    $sessionId
                );
        }

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

                    'auth',
                    'LOGOUT',
                    null,
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

    private function logLogin($user, $status, $sessionId)
    {
        $this->CI->Login_log_model->create([
            'user_id'     => $user->id,
            'session_id'  => $sessionId,
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

        $cooldownSeconds = (int) $this->CI->config->item('otp_send_cooldown_seconds');
        if ($cooldownSeconds < 1) {
            $cooldownSeconds = 60;
        }

        if ($this->CI->Password_reset_model->wasRecentlySent($email, $cooldownSeconds)) {
            // A recent OTP should take the user to verification instead of
            // presenting the resend cooldown as a failed password reset.
            $this->CI->session->set_userdata([
                'reset_email' => $email,
                'otp_verified' => false
            ]);
            return [
                'status' => true,
                'code' => 'cooldown',
                'message' => 'A verification code was sent recently. Check your email and enter it below. You can request another code after one minute.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create OTP
        |--------------------------------------------------------------------------
        */

        $otp = $this->CI->otp_service->createOTP($email);

        if (!$otp) {
            return [
                'status' => false,
                'message' => 'We could not create a verification code. Please try again.'
            ];
        }

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
            // Do not leave an unsent code that blocks a retry during cooldown.
            $this->CI->otp_service->clearOTP($email);
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
                null,
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
        $passwordErrors = $this->CI->password_validator->validate($password);
        if ($passwordErrors) {
            return [
                'status' => false,
                'message' => $this->CI->password_validator->error_message($passwordErrors)
            ];
        }

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

}
