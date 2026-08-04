<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public function get_user($username)
    {
        return $this->db
                    ->select('users.*, roles.role_name')
                    ->from('users')
                    ->join('roles', 'roles.id = users.role_id')
                    ->group_start()
                        ->where('users.username', $username)
                        ->or_where('users.email', $username)
                    ->group_end()
                    ->get()
                    ->row();
    }
    
    public function get_user_by_email($email)
    {
        return $this->db
                    ->where('email', $email)
                    ->get('users')
                    ->row();
    }

    public function save_otp($data)
    {
        return $this->db
                    ->insert(
                        'password_resets',
                        $data
                    );
    }

    public function delete_old_otp($email)
    {
        return $this->db
                    ->where('email', $email)
                    ->delete(
                        'password_resets'
                    );
    }

    public function get_otp($email, $otp)
    {
        return $this->db
                    ->where('email', $email)
                    ->where('otp', $otp)
                    ->order_by('id', 'DESC')
                    ->get('password_resets')
                    ->row();
    }
}