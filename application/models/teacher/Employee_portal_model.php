<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_portal_model extends CI_Model
{
    public function update_profile($user_id, $employee_id, array $data)
    {
        $user_id = (int) $user_id;
        $employee_id = (int) $employee_id;

        $linkedProfile = $this->db
            ->where('id', $employee_id)
            ->where('user_id', $user_id)
            ->count_all_results('employees') > 0;
        if (!$linkedProfile) return ['ok' => false, 'code' => 'invalid_profile'];

        $usernameExists = $this->db
            ->where('username', $data['username'])
            ->where('id !=', $user_id)
            ->count_all_results('users') > 0;
        if ($usernameExists) return ['ok' => false, 'code' => 'duplicate_username'];

        $emailExists = $this->db
            ->where('email', $data['email'])
            ->where('id !=', $user_id)
            ->count_all_results('users') > 0;
        if ($emailExists) return ['ok' => false, 'code' => 'duplicate_email'];

        $this->db->trans_begin();
        $this->db->where('id', $user_id)->update('users', [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] !== '' ? $data['middle_name'] : null,
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email']
        ]);

        $this->db->where('id', $employee_id)->where('user_id', $user_id)->update('employees', [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] !== '' ? $data['middle_name'] : null,
            'last_name' => $data['last_name'],
            'work_email' => $data['email'],
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'updated_by' => (string) $user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true];
    }
}
