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

    /*
    |--------------------------------------------------------------------------
    | Get User By ID
    |--------------------------------------------------------------------------
    */

    public function getById($id)
    {
        return $this->db
                    ->where('id', $id)
                    ->get($this->table)
                    ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Get User By Email
    |--------------------------------------------------------------------------
    */

    public function getByEmail($email)
    {
        return $this->db
                    ->where('email', $email)
                    ->get($this->table)
                    ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Get User By Employee Number
    |--------------------------------------------------------------------------
    */

    public function getByEmployeeNo($employee_no)
    {
        return $this->db
                    ->where('employee_no', $employee_no)
                    ->get($this->table)
                    ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    public function create($data)
    {
        $this->db->insert(
            $this->table,
            $data
        );

        return $this->db->insert_id();
    }

    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update($id, $data)
    {
        return $this->db
                    ->where('id', $id)
                    ->update(
                        $this->table,
                        $data
                    );
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
                    ->update(
                        $this->table,
                        [
                            'password' => $password
                        ]
                    );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Last Login
    |--------------------------------------------------------------------------
    */

    public function updateLastLogin($id)
    {
        return $this->db
                    ->where('id', $id)
                    ->update(
                        $this->table,
                        [
                            'last_login' => date('Y-m-d H:i:s')
                        ]
                    );
    }

    /*
    |--------------------------------------------------------------------------
    | Activate User
    |--------------------------------------------------------------------------
    */

    public function activate($id)
    {
        return $this->update(
            $id,
            [
                'is_active' => 1
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Deactivate User
    |--------------------------------------------------------------------------
    */

    public function deactivate($id)
    {
        return $this->update(
            $id,
            [
                'is_active' => 0
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Check Email Exists
    |--------------------------------------------------------------------------
    */

    public function emailExists($email)
    {
        return $this->db
                    ->where('email', $email)
                    ->count_all_results($this->table) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Active Users
    |--------------------------------------------------------------------------
    */

    public function getActiveUsers()
    {
        return $this->db
                    ->where('is_active', 1)
                    ->get($this->table)
                    ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Delete User (Soft Delete Recommended)
    |--------------------------------------------------------------------------
    */

    public function delete($id)
    {
        return $this->deactivate($id);
    }
}