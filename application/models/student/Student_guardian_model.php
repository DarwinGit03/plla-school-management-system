<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_guardian_model extends CI_Model
{
    protected $table = 'student_guardians';


    public function get_by_student($student_id)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->order_by('is_primary', 'DESC')
            ->order_by('guardian_type', 'ASC')
            ->get($this->table)
            ->result();
    }


    public function get_by_id($id)
    {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }


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


    // public function update($id, $data)
    // {
    //     return $this->db
    //         ->where('id', $id)
    //         ->update(
    //             $this->table,
    //             $data
    //         );
    // }

    public function update($id, $data)
    {
        $existing =
            $this->db
                ->where('id', $id)
                ->get($this->table)
                ->row();

        if (!$existing) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Actual Changes
        |--------------------------------------------------------------------------
        */

        foreach ($data as $field => $value) {

            if (
                in_array(
                    $field,
                    [
                        'created_by',
                        'updated_by',
                        'created_at',
                        'updated_at'
                    ],
                    true
                )
            ) {
                continue;
            }


            if (
                (string) ($existing->$field ?? '') !==
                (string) $value
            ) {

                /*
                |--------------------------------------------------------------
                | Actual change detected
                |--------------------------------------------------------------
                */

                $data['updated_by'] =
                    $this->session
                        ->userdata('employee_no');

                $data['updated_at'] =
                    date('Y-m-d H:i:s');


                /*
                |--------------------------------------------------------------
                | Never modify created_by
                |--------------------------------------------------------------
                */

                unset(
                    $data['created_by'],
                    $data['created_at']
                );


                return $this->db
                    ->where('id', $id)
                    ->update(
                        $this->table,
                        $data
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | No Actual Changes
        |--------------------------------------------------------------------------
        */

        return true;
    }


    public function delete($id)
    {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }
}