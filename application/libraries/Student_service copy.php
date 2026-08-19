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


    public function create_student($student_data,
    $enrollment_data,
    $guardians = [],
    $addresses = []
    ) {

        // log_message(
        //     'debug',
        //     '[STUDENT SERVICE] create_student() started.'
        // );

        $this->CI->db->trans_start();

        /*
        |--------------------------------------------------------------------------
        | Create Student
        |--------------------------------------------------------------------------
        */
                
        // log_message(
        //     'debug',
        //     '[STUDENT SERVICE] Creating student record.'
        // );

        $student_id =
            $this->CI
                ->Student_model
                ->create_student(
                    $student_data
                );


        if (!$student_id) {

            log_message(
                'error',
                '[STUDENT SERVICE] Student creation failed.'
            );

            $this->CI->db->trans_rollback();

            return false;
        }

        // log_message(
        //     'debug',
        //     '[STUDENT SERVICE] Student created. ID: ' .
        //     $student_id
        // );


        /*
        |--------------------------------------------------------------------------
        | Create Enrollment
        |--------------------------------------------------------------------------
        */

        $enrollment_data['student_id'] =
            $student_id;

        // log_message(
        //     'debug',
        //     '[STUDENT SERVICE] Creating enrollment.'
        // );

        $enrollment_id =
            $this->CI
                ->Student_model
                ->create_enrollment(
                    $enrollment_data
                );


        if (!$enrollment_id) {

            log_message(
                'error',
                '[STUDENT SERVICE] Enrollment creation failed. Student ID: ' .
                $student_id
            );

            $this->CI->db->trans_rollback();

            return false;
        }

        // log_message(
        //     'debug',
        //     '[STUDENT SERVICE] Enrollment created. ID: ' .
        //     $enrollment_id
        // );


        /*
        |--------------------------------------------------------------------------
        | Validate Guardians
        |--------------------------------------------------------------------------
        */

        $primary_count = 0;

        // log_message(
        //     'debug',
        //     '[STUDENT SERVICE] Processing guardians.'
        // );

        foreach ($guardians as $guardian) {

            if (!empty($guardian['is_primary'])) {

                $primary_count++;

            }

        }


        if ($primary_count > 1) {

            $this->CI->db->trans_rollback();

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Create Guardians
        |--------------------------------------------------------------------------
        */

        if (!empty($guardians)) {

            foreach ($guardians as $guardian) {

                /*
                | Skip completely empty guardian rows.
                */

                if (
                    empty($guardian['first_name']) &&
                    empty($guardian['last_name'])
                ) {
                    continue;
                }


                /*
                | Attach student ID.
                */

                $guardian['student_id'] =
                    $student_id;


                /*
                | Normalize primary flag.
                */

                $guardian['is_primary'] =
                    !empty($guardian['is_primary'])
                        ? 1
                        : 0;


                /*
                | Insert guardian.
                */

                // log_message(
                //     'debug',
                //     '[STUDENT SERVICE] Creating guardian for student ID: ' .
                //     $student_id
                // );

                $guardian_id =
                    $this->CI
                        ->Student_guardian_model
                        ->create(
                            $guardian
                        );


                if (!$guardian_id) {

                    log_message(
                        'error',
                        '[STUDENT SERVICE] Guardian creation failed. Student ID: ' .
                        $student_id
                    );

                    $this->CI->db->trans_rollback();

                    return false;
                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Create Addresses
        |--------------------------------------------------------------------------
        */

        if (!empty($addresses)) {

            // log_message(
            //     'debug',
            //     '[STUDENT SERVICE] Processing addresses.'
            // );

            foreach (
                $addresses as $address
            ) {

                if (
                    empty($address['house_no']) &&
                    empty($address['street']) &&
                    empty($address['barangay']) &&
                    empty($address['city']) &&
                    empty($address['province'])
                ) {
                    continue;
                }


                $address['student_id'] =
                    $student_id;


                // log_message(
                //     'debug',
                //     '[STUDENT SERVICE] Processing addresses.'
                // );

                $address_id =
                    $this->CI
                        ->Student_address_model
                        ->create($address);

                // log_message(
                //     'debug',
                //     '[STUDENT SERVICE] Creating address for student ID: ' .
                //     $student_id
                // );


                if (!$address_id) {
                     log_message(
                        'error',
                        '[STUDENT SERVICE] Address creation failed. Student ID: ' .
                        $student_id
                    );


                    $this->CI->db->trans_rollback();

                    return false;
                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Complete Transaction
        |--------------------------------------------------------------------------
        */

        $this->CI->db->trans_complete();


        if (
            $this->CI->db->trans_status() === false
        ) {

            log_message(
                'error',
                '[STUDENT SERVICE] Transaction failed. Student ID: ' .
                $student_id
            );

            return false;
        }


        // log_message(
        //     'info',
        //     '[STUDENT SERVICE] Student registration completed. Student ID: ' .
        //     $student_id
        // );


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