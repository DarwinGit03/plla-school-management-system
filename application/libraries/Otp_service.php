<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Otp_service
{
    protected $CI;
    
    private $maxAttempts = 3;
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('auth/Password_reset_model');
    }

    /*
    |--------------------------------------------------------------------------
    | Generate OTP
    |--------------------------------------------------------------------------
    */

    public function generate()
    {
        return random_int(100000,999999);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Expiration
    |--------------------------------------------------------------------------
    */

    // public function expires()
    // {
    //     return
    //         date(
    //             'Y-m-d H:i:s',
    //             strtotime(
    //                 '+3 minutes'
    //             )
    //         );
    // }
    private function expiration()
    {
        return
            date(
                'Y-m-d H:i:s',
                strtotime('+5 minutes')
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Check Expired
    |--------------------------------------------------------------------------
    */

    public function isExpired($reset)
    {
        return strtotime($reset->expires_at) < time();
    }

    /*
    |--------------------------------------------------------------------------
    | Check Attempts
    |--------------------------------------------------------------------------
    */

    public function attemptsExceeded($reset)
    {
        return $reset->attempts >= 3;
    }

    /*
    |--------------------------------------------------------------------------
    | Increment Attempts
    |--------------------------------------------------------------------------
    */

    public function incrementAttempts(
        $id
    )
    {
        $this->CI
             ->db
             ->set(
                 'attempts',
                 'attempts+1',
                 FALSE
             )
             ->where(
                 'id',
                 $id
             )
             ->update(
                 'password_resets'
             );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate OTP
    |--------------------------------------------------------------------------
    */

    private function generateOTP()
    {
        return random_int(
            100000,
            999999
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save OTP
    |--------------------------------------------------------------------------
    */
    

    public function createOTP($email)
    {
        $otp =
            $this->generateOTP();

        $expires_at =
            $this->expiration();

        $record =
            $this->CI
                ->Password_reset_model
                ->getByEmail(
                    $email
                );

        if($record)
        {
            $this->CI
                ->Password_reset_model
                ->updateOTP(
                    $email,
                    $otp,
                    $expires_at
                );
        }
        else
        {
            $this->CI
                ->Password_reset_model
                ->create([

                    'email'       => $email,
                    'otp'         => $otp,
                    'attempts'    => 0,
                    'expires_at'  => $expires_at,
                    'created_at'  => date('Y-m-d H:i:s')

                ]);
        }

        return $otp;
    }

    /*
    |--------------------------------------------------------------------------
    | Verify OTP
    |--------------------------------------------------------------------------
    */

    public function verifyOTP($email,$otp)
    {
        $record =
            $this->CI
                ->Password_reset_model
                ->getByEmail(
                    $email
                );

        if(!$record)
        {
            return [

                'status'=>false,

                'message'=>'OTP not found.'

            ];
        }

        if(
            strtotime($record->expires_at)
            <
            time()
        )
        {
            return [

                'status'=>false,

                'message'=>'OTP expired.'

            ];
        }

        if(
            $record->otp !=
            $otp
        )
        {
            return [

                'status'=>false,

                'message'=>'Invalid OTP.'

            ];
        }

        return [

            'status'=>true

        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Clear OTP
    |--------------------------------------------------------------------------
    */

    public function resendOTP($email)
    {
        /*
        |--------------------------------------------------------------------------
        | Generate OTP
        |--------------------------------------------------------------------------
        */

        $otp = random_int(100000, 999999);

        /*
        |--------------------------------------------------------------------------
        | Save OTP
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->Password_reset_model
            ->updateOTP(
                $email,
                $otp
            );

        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        $mail =
            $this->CI
                ->mail_service
                ->sendOTP(
                    $email,
                    $otp
                );

        if(!$mail['status'])
        {
            return [

                'status' => false,

                'message' => $mail['message']

            ];
        }

        return [

            'status' => true,

            'message' => 'A new OTP has been sent to your email.'

        ];
    }

    public function clearOTP($email)
    {
        return
            $this->CI
                ->Password_reset_model
                ->delete(
                    $email
                );
    }


}