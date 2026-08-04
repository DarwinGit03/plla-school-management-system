<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_log_model extends CI_Model
{
    protected $table = 'login_logs';

    /*
    |--------------------------------------------------------------------------
    | Create Login Log
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
    | Update Logout Time
    |--------------------------------------------------------------------------
    */

    public function updateLogout($user_id)
    {
        return $this->db
                    ->where('user_id', $user_id)
                    ->where('logout_time IS NULL', NULL, FALSE)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->update(
                        $this->table,
                        [
                            'logout_time' => date('Y-m-d H:i:s')
                        ]
                    );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Latest Login
    |--------------------------------------------------------------------------
    */

    public function getLatest($user_id)
    {
        return $this->db
                    ->where('user_id', $user_id)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get($this->table)
                    ->row();
    }
}