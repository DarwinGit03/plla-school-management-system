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
}