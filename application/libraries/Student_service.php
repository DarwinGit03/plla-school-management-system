<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->model('Student/Student_model');
        $this->CI->load->model('Student/Student_guardian_model');

        $this->CI->load->model('Student/Student_address_model');
    }

    /**
     * Get students.
     */
    public function get_students($filters = [], $limit = null, $offset = null)
    {
        return $this->CI
            ->Student_model
            ->get_students(
                $filters,
                $limit,
                $offset
            );
    }

    /**
     * Count students.
     */
    public function count_students($filters = [])
    {
        return $this->CI
            ->Student_model
            ->count_students($filters);
    }

    /**
     * Get single student.
     */
    public function get_student($id)
    {
        return $this->CI
            ->Student_model
            ->get_student($id);
    }

    /**
     * Create a complete student registration.
     *
     * All related records are saved in one database transaction.
     *
     * @param array $student_data
     * @param array $enrollment_data
     * @param array $guardians
     * @param array $addresses
     * @return int|false
     */
    public function create_student(
        array $student_data,
        array $enrollment_data,
        array $guardians = [],
        array $addresses = []
    ) {
        /*
        |--------------------------------------------------------------------------
        | Start Transaction
        |--------------------------------------------------------------------------
        */

        $this->CI->db->trans_begin();


        /*
        |--------------------------------------------------------------------------
        | Create Student
        |--------------------------------------------------------------------------
        */

        $student_id =
            $this->CI
                ->Student_model
                ->create_student(
                    $student_data
                );


        if (!$student_id) {

            $this->CI->db->trans_rollback();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Create Enrollment
        |--------------------------------------------------------------------------
        */

        $enrollment_data['student_id'] =
            $student_id;


        $enrollment_id =
            $this->CI
                ->Student_model
                ->create_enrollment(
                    $enrollment_data
                );


        if (!$enrollment_id) {

            $this->CI->db->trans_rollback();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Create Guardians
        |--------------------------------------------------------------------------
        */

        foreach ($guardians as $guardian) {

            $guardian['student_id'] =
                $student_id;


            $guardian['is_primary'] =
                !empty(
                    $guardian['is_primary']
                )
                    ? 1
                    : 0;


            $guardian_id =
                $this->CI
                    ->Student_guardian_model
                    ->create(
                        $guardian
                    );


            if (!$guardian_id) {

                $this->CI->db->trans_rollback();

                return false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Create Addresses
        |--------------------------------------------------------------------------
        */

        foreach ($addresses as $address) {

            $address['student_id'] =
                $student_id;


            $address_id =
                $this->CI
                    ->Student_address_model
                    ->create(
                        $address
                    );


            if (!$address_id) {

                $this->CI->db->trans_rollback();

                return false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Check Transaction
        |--------------------------------------------------------------------------
        */

        if (
            $this->CI->db->trans_status() === false
        ) {

            $this->CI->db->trans_rollback();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Commit Transaction
        |--------------------------------------------------------------------------
        */

        $this->CI->db->trans_commit();


        return $student_id;
    }

    /**
     * Get complete student profile.
     *
     * @param int $id
     * @return object|null
     */
    public function get_student_profile($id)
    {
        return $this->CI
            ->Student_model
            ->get_student_profile($id);
    }

    /**
     * Get student guardians.
     */
    public function get_guardians($student_id)
    {
        return $this->CI
            ->Student_guardian_model
            ->get_by_student($student_id);
    }


    /**
     * Get student addresses.
     */
    public function get_addresses($student_id)
    {
        return $this->CI
            ->Student_address_model
            ->get_by_student($student_id);
    }

    /**
     * Get student by LRN.
     *
     * @param string $lrn
     * @return object|null
     */
    public function get_student_by_lrn($lrn)
    {
        return $this->CI
            ->Student_model
            ->get_student_by_lrn($lrn);
    }


    /**
     * Get unique academic years.
     */
    public function get_academic_years()
    {
        return $this->CI
            ->Student_model
            ->get_academic_years();
    }


    /**
     * Get grade levels for an academic year.
     */
    public function get_grade_levels_by_year($year)
    {
        return $this->CI
            ->Student_model
            ->get_grade_levels_by_year($year);
    }


    /**
     * Get sections for academic year + grade.
     */
    public function get_sections_by_year_and_grade(
        $year,
        $grade
    ) {
        return $this->CI
            ->Student_model
            ->get_sections_by_year_and_grade(
                $year,
                $grade
            );
    }
}