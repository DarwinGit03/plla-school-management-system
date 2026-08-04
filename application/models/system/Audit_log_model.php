<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_model extends CI_Model
{
    protected $table = 'audit_logs';

    /*
    |--------------------------------------------------------------------------
    | Create Audit Log
    |--------------------------------------------------------------------------
    */

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Logs
    |--------------------------------------------------------------------------
    */

    public function getAll()
    {
        return $this->db
                    ->order_by('id','DESC')
                    ->get($this->table)
                    ->result();
    }
}