<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    /*
    |--------------------------------------------------------------------------
    | Update Session Token
    |--------------------------------------------------------------------------
    */

    public function updateSessionToken(
        $user_id,
        $token
    )
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

    /*
    |--------------------------------------------------------------------------
    | Get User By ID
    |--------------------------------------------------------------------------
    */

    public function getById(
        $id
    )
    {
        return $this->db

            ->where(
                'id',
                $id
            )

            ->get(
                'users'
            )

            ->row();
    }
}