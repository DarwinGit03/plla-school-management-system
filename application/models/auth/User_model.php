<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    /*
    |--------------------------------------------------------------------------
    | User_model should only interact with the users table. It should never send emails, manage sessions, or perform redirects.
    | 
    | Table Name
    |--------------------------------------------------------------------------
    */

    protected $table = 'users';
    
    public function __construct()
    {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Get User By Email
    |--------------------------------------------------------------------------
    */

    public function getByEmail($email)
    {
        return $this->db->where('email', $email)->get('users')->row();
    }
    
    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword($id, $password)
    {
        return $this->db
                    ->where('id', $id)
                    ->update('users', [

                        'password' => $password

                    ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Last Login
    |--------------------------------------------------------------------------
    */

    public function updateLastLogin($user_id)
    {
        return $this->db->where('id', $user_id)->update('users', 
            [
                'last_login' => date('Y-m-d H:i:s')
            ]

        );
    }

    public function getById($id)
    {
        return $this->db
                    ->where('id', $id)
                    ->get($this->table)
                    ->row();
    }
    /*
    |--------------------------------------------------------------------------
    | Increase Failed Attempts
    |--------------------------------------------------------------------------
    */

    public function increaseFailedAttempts($user_id)
    {

        $this->db
            ->set(
                'failed_attempts',
                'failed_attempts+1',
                FALSE
            );

        $this->db->set(

            'failed_attempt_at',

            date('Y-m-d H:i:s')

        );

        $this->db->where(

            'id',

            $user_id

        );

        return $this->db->update('users');
        // $this->db->set(

        //     'failed_attempts',

        //     'failed_attempts+1',

        //     false

        // );

        // $this->db->where(

        //     'id',

        //     $user_id

        // );

        // return $this->db->update(

        //     'users'

        // );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Failed Attempts
    |--------------------------------------------------------------------------
    */

    public function resetFailedAttempts($user_id)
    {
        return $this->db
        ->where('id',$user_id)
        ->update(
            'users',
            [
                'failed_attempts'=>0,
                'failed_attempt_at' => NULL,
                'locked_until'=>NULL
            ]

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lock Account
    |--------------------------------------------------------------------------
    */

    public function lockAccount($user_id)
    {
        return $this->db
                ->where('id',$user_id)
                ->update(

                    'users',

                    [

                        'locked_until'=>

                            date(

                                'Y-m-d H:i:s',

                                // strtotime('+15 minutes')
                                strtotime(
                                    '+' .
                                    $this->config->item(
                                        'lock_minutes'
                                    ) . ' minutes'
                                )

                            )

                    ]

                );
    }

    /*
    |--------------------------------------------------------------------------
    | Is Account Locked
    |--------------------------------------------------------------------------
    */

    public function isLocked($user)
    {
        if(empty($user->locked_until))
        {
            return false;
        }

        return strtotime($user->locked_until) > time();
    }

    /*
    |--------------------------------------------------------------------------
    | Remaining Lock Time
    |--------------------------------------------------------------------------
    */

    public function remainingLockMinutes($user)
    {
        if(empty($user->locked_until))
        {
            return 0;
        }

        $seconds =

            strtotime($user->locked_until)

            -

            time();

        return max(

            0,

            ceil($seconds / 60)

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Unlock Account
    |--------------------------------------------------------------------------
    */

    public function unlockAccount($user_id)
    {
        return $this->db
            ->where(
                'id',
                $user_id
            )
            ->update(
                'users',
                [
                    'failed_attempts' => 0,
                    'locked_until'    => NULL
                ]
            );
    }

    public function lockExpired($user)
    {
        if(empty($user->locked_until))
        {
            return false;
        }

        return strtotime($user->locked_until) <= time();
    }

    /*
    |--------------------------------------------------------------------------
    | Update Session Token
    |--------------------------------------------------------------------------
    */

    public function updateSessionToken($user_id, $token)
    {
        return $this->db
            ->where(
                'id',
                $user_id
            )
            ->update(
                'users',
                [
                    'session_token' => $token
                ]
            );
    }

    public function shouldResetFailedAttempts($user_id)
    {
        if(empty($user_id->failed_attempt_at))
        {
            return false;
        }

        $minutes =
            (
                time() -
                strtotime(
                    $user_id->failed_attempt_at
                )

            ) / 60;

        return

            $minutes >=
            $this->config->item(
                'failed_attempt_reset_minutes'
            );
    }


}