<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password_reset_model extends CI_Model
{
    protected $table = 'password_resets';
    
    /*
    |--------------------------------------------------------------------------
    | Get Reset Request
    |--------------------------------------------------------------------------
    */

    public function getByEmail($email)
    {
        return $this->db
            ->where(
                'email',
                $email
            )
            ->get(
                'password_resets'
            )
            ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Password Reset
    |--------------------------------------------------------------------------
    */

    public function create($data)
    {
        return $this->db->insert(
            $this->table,
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Password Reset
    |--------------------------------------------------------------------------
    */

    public function update($email, $data)
    {
        return $this->db
                    ->where('email', $email)
                    ->update(
                        $this->table,
                        $data
                    );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Password Reset
    |--------------------------------------------------------------------------
    */

    public function delete($email)
    {
        return $this->db
                    ->where('email', $email)
                    ->delete($this->table);
    }

    /*
    |--------------------------------------------------------------------------
    | Increment Attempts
    |--------------------------------------------------------------------------
    */

    public function incrementAttempts($email)
    {
        $this->db->set(
            'attempts',
            'attempts + 1',
            FALSE
        );

        return $this->db
                    ->where('email', $email)
                    ->update($this->table);
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Attempts
    |--------------------------------------------------------------------------
    */

    public function resetAttempts($email)
    {
        return $this->db
                    ->where('email', $email)
                    ->update(
                        $this->table,
                        [
                            'attempts' => 0
                        ]
                    );
    }

    /*
    |--------------------------------------------------------------------------
    | Update OTP
    |--------------------------------------------------------------------------
    */

    public function updateOTP(
        $email,
        $otp
    )
    {
        return $this->db

            ->where(
                'email',
                $email
            )

            ->update(
                'password_resets',
                [

                    'otp'=>$otp,

                    'expires_at'=>

                        date(

                            'Y-m-d H:i:s',

                            strtotime('+5 minutes')

                        )

                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete All Reset Requests
    |--------------------------------------------------------------------------
    */

    public function deleteAllByEmail($email)
    {
        return $this->db

            ->where(
                'email',
                $email
            )

            ->delete(
                'password_resets'
            );
    }

    // public function updateOTP($email, $otp)
    // {
    //     return $this->db

    //         ->where(
    //             'email',
    //             $email
    //         )

    //         ->update(
    //             'password_resets',
    //             [

    //                 'otp' => $otp,

    //                 'expires_at' => date(
    //                     'Y-m-d H:i:s',
    //                     strtotime('+5 minutes')
    //                 )

    //             ]
    //         );
    // }

    /*
    |--------------------------------------------------------------------------
    | Delete Password Reset Record
    |--------------------------------------------------------------------------
    */

    public function deleteByEmail($email)
    {
        return $this->db
            ->where(
                'email',
                $email
            )
            ->delete(
                'password_resets'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Expired OTPs
    |--------------------------------------------------------------------------
    */

    public function deleteExpired()
    {
        return $this->db

            ->where(

                'expires_at <',

                date('Y-m-d H:i:s')

            )

            ->delete(

                'password_resets'

            );
    }



    // public function updateOTP($email, $otp, $expires_at)
    // {
    //     return $this->db
    //                 ->where('email', $email)
    //                 ->update(
    //                     $this->table,
    //                     [
    //                         'otp'        => $otp,
    //                         'expires_at' => $expires_at,
    //                         'attempts'   => 0
    //                     ]
    //                 );
    // }
}