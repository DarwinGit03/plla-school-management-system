<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_address_model extends CI_Model
{
    protected $table = 'student_addresses';


    public function get_by_student($student_id)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->order_by('address_type', 'ASC')
            ->get($this->table)
            ->result();
    }


    // public function create($data)
    // {
    //     $this->db->insert(
    //         $this->table,
    //         $data
    //     );

    //     if ($this->db->affected_rows() > 0) {

    //         return $this->db->insert_id();
    //     }

    //     return false;
    // }

    public function create($data)
    {
        $this->db->insert(
            $this->table,
            $data
        );


        if (
            $this->db->affected_rows() <= 0
        ) {

            return false;
        }


        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', $id)
            ->update(
                $this->table,
                $data
            );
    }


    public function delete($id)
    {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }
}