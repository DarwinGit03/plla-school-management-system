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
        //test now
        $employee_no =
        $this->CI
            ->session
            ->userdata('employee_no');

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
        
        //test now
        $student_data['created_by'] =
            $employee_no;

        // $student_data['updated_by'] =
        //     $employee_no;

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

        $enrollment_data['student_id'] = $student_id;
        //test now
        $enrollment_data['created_by'] =
            $employee_no;

        // $enrollment_data['updated_by'] =
        //     $employee_no;

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

            // if (
            //     !$this->has_address_data($address)
            // ) {
            //     continue;
            // }
            

            $guardian['student_id'] =
                $student_id;

            $guardian['lrn'] = 
                $student_data['lrn'];
                
            // $guardian['created_by'] =
            //     $employee_no;

            $guardian['created_by'] =
                $employee_no;

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
                
            $address['created_by'] =
                $employee_no;

            // $address['updated_by'] =
            //     $employee_no;

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

        /*
        |--------------------------------------------------------------------------
        | Get Updated Student For Audit
        |--------------------------------------------------------------------------
        */

        $new_student =
            $this->CI
                ->Student_model
                ->get_student(
                    $student_id
                );

        if (!$new_student) {
            return true;
        }

        $new_enrollment =
            $this->CI
                ->Student_model
                ->get_current_enrollment(
                    $student_id
                );

        $new_guardians =
            $this->CI
                ->Student_guardian_model
                ->get_by_student(
                    $student_id
                );

        $new_addresses =
            $this->CI
                ->Student_address_model
                ->get_by_student(
                    $student_id
                );


        $details = [
            'student' => $new_student,
            'enrollment' => $new_enrollment,
            'guardians' => $new_guardians,
            'addresses' => $new_addresses
        ];

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->audit_log_service
            ->log(
                'libraries/Student_service',
                'CREATE_STUDENT',
                $student_id,
                'Student created.',
                $details
            );


        return $student_id;
    }

    /**
     * Update complete student registration.
     *
     * All related records are updated in one database transaction.
     *
     * @param int   $student_id
     * @param array $student_data
     * @param array $enrollment_data
     * @param array $guardians
     * @param array $addresses
     * @return bool
     */
    public function update_student(
        $student_id,
        array $student_data,
        array $enrollment_data,
        array $guardians = [],
        array $addresses = []
    ) {
        //test now
        $employee_no =
        $this->CI
            ->session
            ->userdata('employee_no');

        /*
        |--------------------------------------------------------------------------
        | Get Existing Student For Audit
        |--------------------------------------------------------------------------
        */

       $old_student =
            $this->CI
                ->Student_model
                ->get_student(
                    $student_id
                );

        if (!$old_student) {
            return false;
        }

        $old_enrollment =
            $this->CI
                ->Student_model
                ->get_current_enrollment(
                    $student_id
                );

        $old_guardians =
            $this->CI
                ->Student_guardian_model
                ->get_by_student(
                    $student_id
                );

        $old_addresses =
            $this->CI
                ->Student_address_model
                ->get_by_student(
                    $student_id
                );

        /*
        |--------------------------------------------------------------------------
        | Start Transaction
        |--------------------------------------------------------------------------
        */

        $this->CI->db->trans_begin();


        /*
        |--------------------------------------------------------------------------
        | Update Student
        |--------------------------------------------------------------------------
        */

        // $student_data['updated_by'] = $employee_no;

        $updated =
            $this->CI
                ->Student_model
                ->update_student(
                    $student_id,
                    $student_data
                );


        if (!$updated) {

            $this->CI->db->trans_rollback();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Update Enrollment
        |--------------------------------------------------------------------------
        */

        //test now
        // $enrollment_data['updated_by'] = $employee_no;

        $updated =
            $this->CI
                ->Student_model
                ->update_enrollment(
                    $student_id,
                    $enrollment_data
                );


        if (!$updated) {

            $this->CI->db->trans_rollback();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Guardians
        |--------------------------------------------------------------------------
        */

        $existing_guardians =
            $this->CI
                ->Student_guardian_model
                ->get_by_student(
                    $student_id
                );


        $existing_guardian_ids = [];


        foreach ($existing_guardians as $guardian) {

            $existing_guardian_ids[] =
                (int) $guardian->id;
        }


        $submitted_guardian_ids = [];


        /*
        |--------------------------------------------------------------------------
        | Update / Create Guardians
        |--------------------------------------------------------------------------
        */

        foreach ($guardians as $guardian) {

            $guardian['student_id'] =
                $student_id;

            $guardian['lrn'] =
                $student_data['lrn'] ?? null;

            // $guardian['updated_by'] =
            //     $employee_no;

            $guardian['is_primary'] =
                !empty(
                    $guardian['is_primary']
                )
                    ? 1
                    : 0;


            /*
            |----------------------------------------------------------------------
            | Existing Guardian
            |----------------------------------------------------------------------
            */

            // echo '<pre>';

            // echo "GUARDIAN ID DATA\n";
            // var_dump($guardian['id']);

            // exit;


            if (
                !empty($guardian['id'])
            ) {

                $guardian_id =
                    (int) $guardian['id'];

                $submitted_guardian_ids[] =
                    $guardian_id;


                /*
                | Only allow updating guardians
                | belonging to this student.
                */

                if (
                    !in_array(
                        $guardian_id,
                        $existing_guardian_ids,
                        true
                    )
                ) {

                    $this->CI->db->trans_rollback();

                    return false;
                }


                unset(
                    $guardian['id']
                );

                $updated =
                    $this->CI
                        ->Student_guardian_model
                        ->update(
                            $guardian_id,
                            $guardian
                        );


                if (!$updated) {

                    $this->CI->db->trans_rollback();

                    return false;
                }

            } 
            else {

                /*
                |------------------------------------------------------------------
                | New Guardian
                |------------------------------------------------------------------
                */
                $guardian['created_by'] =
                    $employee_no;

                $guardian['updated_by'] =
                    $employee_no;

                unset(
                    $guardian['id']
                );

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
        | Delete Removed Guardians
        |--------------------------------------------------------------------------
        */

        foreach (
            $existing_guardian_ids
            as $existing_id
        ) {

            if (
                !in_array(
                    $existing_id,
                    $submitted_guardian_ids,
                    true
                )
            ) {

                $deleted =
                    $this->CI
                        ->Student_guardian_model
                        ->delete(
                            $existing_id
                        );


                if (!$deleted) {

                    $this->CI->db->trans_rollback();

                    return false;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update Addresses
        |--------------------------------------------------------------------------
        */

        foreach ($addresses as $address) {

            if (empty($address['id'])) {

                $address['student_id'] =
                    $student_id;
            
                // $address['updated_by'] =
                //     $employee_no;

                unset(
                    $address['id']
                );


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

            } else {

                // $address['updated_by'] =
                //     $employee_no;

                $address_id =
                    (int) $address['id'];


                unset(
                    $address['id']
                );


                $updated =
                    $this->CI
                        ->Student_address_model
                        ->update(
                            $address_id,
                            $student_id,
                            $address
                        );


                if (!$updated) {

                    $this->CI->db->trans_rollback();

                    return false;
                }
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
        | Commit
        |--------------------------------------------------------------------------
        */

        $this->CI->db->trans_commit();
        
        /*
        |--------------------------------------------------------------------------
        | Get Updated Student For Audit
        |--------------------------------------------------------------------------
        */

        $new_student =
            $this->CI
                ->Student_model
                ->get_student(
                    $student_id
                );

        if (!$new_student) {
            return true;
        }

        $new_enrollment =
            $this->CI
                ->Student_model
                ->get_current_enrollment(
                    $student_id
                );

        $new_guardians =
            $this->CI
                ->Student_guardian_model
                ->get_by_student(
                    $student_id
                );

        $new_addresses =
            $this->CI
                ->Student_address_model
                ->get_by_student(
                    $student_id
                );

        /*
        |--------------------------------------------------------------------------
        | Compare Changes
        |--------------------------------------------------------------------------
        */

        $student_details =
            $this->CI
                ->audit_log_service
                ->get_changed_fields(
                    $old_student,
                    $new_student
                );

        $enrollment_details =
            $this->CI
                ->audit_log_service
                ->get_changed_fields(
                    $old_enrollment,
                    $new_enrollment
                );  

        $guardian_details =
            $this->CI
                ->audit_log_service
                ->get_changed_records(
                    $old_guardians,
                    $new_guardians
                );

        $address_details =
            $this->CI
                ->audit_log_service
                ->get_changed_records(
                    $old_addresses,
                    $new_addresses
                );

        $details = [];
        
        if (
            !empty($student_details) ||
            !empty($enrollment_details) ||
            !empty($guardian_details) ||
            !empty($address_details)
        ) {
            $details = [
                'student_lrn' =>
                    $new_student->lrn ?? null
            ];
        }

        if (!empty($student_details)) {
            $details['student'] = $student_details;
        }

        if (!empty($enrollment_details)) {
            $details['enrollment'] = $enrollment_details;
        }

        if (!empty($guardian_details)) {
            $details['guardians'] = $guardian_details;
        }

        if (!empty($address_details)) {
            $details['addresses'] = $address_details;
        }
        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */
        
        if (!empty($details)) {
            $this->CI
                ->audit_log_service
                ->log(
                    'students',
                    'UPDATE',
                    $student_id,
                    'Student information updated.',
                    $details
                );
        }

        return true;
    }

    public function change_status(
        $student_id,
        $new_status,
        $reason
    ) {
        $new_status =
            strtolower(
                trim($new_status)
            );

        $reason =
            trim($reason);

        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        $allowed_statuses = [
            'active',
            'inactive'
        ];

        if (
            !in_array(
                $new_status,
                $allowed_statuses,
                true
            )
        ) {
            return [
                'status' => false,
                'message' => 'Invalid student status.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Reason
        |--------------------------------------------------------------------------
        */

        if ($reason === '') {
            return [
                'status' => false,
                'message' => 'Reason is required.'
            ];
        }

        if (strlen($reason) > 500) {
            return [
                'status' => false,
                'message' =>
                    'Reason must not exceed 500 characters.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Get Current Student
        |--------------------------------------------------------------------------
        */

        $student =
            $this->get_student($student_id);

        if (!$student) {
            return [
                'status' => false,
                'message' => 'Student not found.'
            ];
        }

        $old_status =
            strtolower(
                trim($student->status)
            );

        /*
        |--------------------------------------------------------------------------
        | Prevent Same Status
        |--------------------------------------------------------------------------
        */

        if ($old_status === $new_status) {
            return [
                'status' => false,
                'message' =>
                    'Student is already ' .
                    ucfirst($new_status) .
                    '.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $employee_no =
            $this->CI
                ->session
                ->userdata(
                    'employee_no'
                );

        $this->CI
            ->db
            ->trans_begin();

        $updated =
            $this->CI
                ->Student_model
                ->change_status(
                    $student_id,
                    $new_status,
                    $employee_no
                );

        if (!$updated) {

            $this->CI
                ->db
                ->trans_rollback();

            return [
                'status' => false,
                'message' =>
                    'Unable to change student status.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction Check
        |--------------------------------------------------------------------------
        */

        if (
            $this->CI
                ->db
                ->trans_status() === false
        ) {

            $this->CI
                ->db
                ->trans_rollback();

            return [
                'status' => false,
                'message' =>
                    'Unable to change student status.'
            ];
        }

        $this->CI
            ->db
            ->trans_commit();

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        $this->CI
            ->audit_log_service
            ->log(
                'students',
                'STATUS_CHANGE',
                $student_id,
                'Student status changed.',
                [
                    'student_lrn' =>
                        $student->lrn,

                    'status' => [
                        'old' => $old_status,
                        'new' => $new_status
                    ],

                    'reason' => $reason
                ]
            );

        return [
            'status' => true,
            'message' =>
                'Student status changed successfully.'
        ];
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

    /**
     * Determine whether an address contains user data.
     *
     */
    // private function has_address_data(array $address)
    // {
    //     $fields = [
    //         'house_no',
    //         'street',
    //         'barangay',
    //         'city',
    //         'province',
    //         'postal_code'
    //     ];

    //     foreach ($fields as $field) {

    //         if (
    //             isset($address[$field])
    //             &&
    //             trim((string) $address[$field]) !== ''
    //         ) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    /**
     * Get student enrollment history.
     *
     * @param int $student_id
     * @return array
     */
    public function get_enrollment_history($student_id)
    {
        return $this->CI
            ->Student_model
            ->get_enrollment_history(
                $student_id
            );
    }
}