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

    // /**
    //  * Create student and enrollment.
    //  *
    //  * @param array $student_data
    //  * @param array $enrollment_data
    //  * @return int|false
    //  */
    // public function create_student(
    //     $student_data,
    //     $enrollment_data
    // ) {
    //     $this->CI->db->trans_start();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Create Student
    //     |--------------------------------------------------------------------------
    //     */

    //     $student_id =
    //         $this->CI
    //             ->Student_model
    //             ->create_student(
    //                 $student_data
    //             );

    //     if (!$student_id) {

    //         $this->CI->db->trans_rollback();

    //         return false;
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Create Enrollment
    //     |--------------------------------------------------------------------------
    //     */

    //     $enrollment_data['student_id'] =
    //         $student_id;

    //     $enrollment_id =
    //         $this->CI
    //             ->Student_model
    //             ->create_enrollment(
    //                 $enrollment_data
    //             );


    //     if (!$enrollment_id) {

    //         $this->CI->db->trans_rollback();

    //         return false;
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Complete Transaction
    //     |--------------------------------------------------------------------------
    //     */

    //     $this->CI->db->trans_complete();


    //     if ($this->CI->db->trans_status() === false) {

    //         return false;
    //     }


    //     return $student_id;
    // }

    /**
     * Create student, enrollment, guardian and addresses.
     *
     * All records are saved inside one database transaction.
     *
     * @param array $student_data
     * @param array $enrollment_data
     * @param array $guardian_data
     * @param array $address_data
     * @return int|false
     */
    // public function create_student(
    //     $student_data,
    //     $enrollment_data,
    //     $guardian_data = [],
    //     $address_data = []
    // ) {
    //     $this->CI->db->trans_start();


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Create Student
    //     |--------------------------------------------------------------------------
    //     */

    //     $student_id =
    //         $this->CI
    //             ->Student_model
    //             ->create_student(
    //                 $student_data
    //             );


    //     if (!$student_id) {

    //         $this->CI->db->trans_rollback();

    //         return false;
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Create Enrollment
    //     |--------------------------------------------------------------------------
    //     */

    //     $enrollment_data['student_id'] =
    //         $student_id;


    //     $enrollment_id =
    //         $this->CI
    //             ->Student_model
    //             ->create_enrollment(
    //                 $enrollment_data
    //             );


    //     if (!$enrollment_id) {

    //         $this->CI->db->trans_rollback();

    //         return false;
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Create Guardian
    //     |--------------------------------------------------------------------------
    //     */

    //     if (!empty($guardian_data)) {

    //         $guardian_data['student_id'] =
    //             $student_id;

    //         $guardian_data['created_at'] =
    //             date('Y-m-d H:i:s');


    //         $guardian_id =
    //             $this->CI
    //                 ->Student_guardian_model
    //                 ->create(
    //                     $guardian_data
    //                 );


    //         if (!$guardian_id) {

    //             $this->CI->db->trans_rollback();

    //             return false;
    //         }
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Create Addresses
    //     |--------------------------------------------------------------------------
    //     */

    //     if (!empty($address_data)) {

    //         foreach ($address_data as $address) {

    //             if (empty($address)) {
    //                 continue;
    //             }


    //             $address['student_id'] =
    //                 $student_id;

    //             $address['created_at'] =
    //                 date('Y-m-d H:i:s');


    //             $address_id =
    //                 $this->CI
    //                     ->Student_address_model
    //                     ->create(
    //                         $address
    //                     );


    //             if (!$address_id) {

    //                 $this->CI->db->trans_rollback();

    //                 return false;
    //             }
    //         }
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Complete Transaction
    //     |--------------------------------------------------------------------------
    //     */

    //     $this->CI->db->trans_complete();


    //     if ($this->CI->db->trans_status() === false) {

    //         return false;
    //     }


    //     return $student_id;
    // }


    public function create_student(
    $student_data,
    $enrollment_data,
    $guardians = [],
    $addresses = []
    ) {
        $this->CI->db->trans_start();


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

        // if (!empty($guardians)) {

        //     foreach (
        //         $guardians as $guardian
        //     ) {

        //         if (
        //             empty($guardian['first_name']) &&
        //             empty($guardian['last_name'])
        //         ) {
        //             continue;
        //         }


        //         $guardian['student_id'] =
        //             $student_id;


        //         /*
        //         | Convert checkbox value.
        //         */

        //         $guardian['is_primary'] =
        //             !empty(
        //                 $guardian['is_primary']
        //             )
        //             ? 1
        //             : 0;


        //         $guardian_id =
        //             $this->CI
        //                 ->Student_guardian_model
        //                 ->create(
        //                     $guardian
        //                 );


        //         if (!$guardian_id) {

        //             $this->CI->db->trans_rollback();

        //             return false;
        //         }

        //     }

        // }

        /*
        |--------------------------------------------------------------------------
        | Validate Guardians
        |--------------------------------------------------------------------------
        */

        $primary_count = 0;

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

        }


        /*
        |--------------------------------------------------------------------------
        | Create Addresses
        |--------------------------------------------------------------------------
        */

        if (!empty($addresses)) {

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

            return false;
        }


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