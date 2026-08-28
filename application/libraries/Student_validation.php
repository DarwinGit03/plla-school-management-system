<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Student_validation
{
    protected $CI;

    protected $errors = [];

    protected $student_id = null;


    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->library(
            'form_validation'
        );

        $this->CI->form_validation->set_error_delimiters('', '');
    }


    /**
     * Validate the complete student registration.
     *
     * @param array|null $guardians
     * @return bool
     */
    public function validate_registration(
        $guardians = null,
        $student_id = null
    )
    {
        $this->errors = [];
        
        $this->student_id = $student_id;


        /*
        |------------------------------------------------------------------
        | Standard Student Form Validation
        |------------------------------------------------------------------
        */

        $this->set_student_detail_rules();

        $this->set_contact_rules();

        // $this->set_address_rules(); -soon

        if (
            $this->CI->form_validation->run() === false
        ) {

            $this->errors['student'] =
                validation_errors(
                    '',
                    ''
                );
        }


        /*
        |------------------------------------------------------------------
        | Guardian Validation
        |------------------------------------------------------------------
        */

        if ($guardians === null) {

            $guardians =
                $this->CI->input->post(
                    'guardians'
                );
        }


        $guardian_errors =
            $this->validate_guardians(
                $guardians
            );


        if (!empty($guardian_errors)) {

            $this->errors['guardians'] =
                $guardian_errors;
        }


        return empty(
            $this->errors
        );
    }


    /**
     * Set student detail validation rules.
     */
    protected function set_student_detail_rules()
    {
        /*
        |------------------------------------------------------------------
        | LRN
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'lrn',
            'LRN',
            'required|trim|numeric|callback_lrn_unique',
            [
                'required' =>
                    'The LRN field is required.',

                'numeric' =>
                    'The LRN must contain numbers only.'
            ]
        );


        /*
        |------------------------------------------------------------------
        | Student Number
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'student_no',
            'Student Number',
            'required|trim|numeric',
            [
                'required' =>
                    'The Student Number field is required.',

                'numeric' =>
                    'The Student Number must contain numbers only.'
            ]
        );


        /*
        |------------------------------------------------------------------
        | First Name
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'first_name',
            'First Name',
            "required|trim|regex_match[/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/]",
            [
                'required' =>
                    'The First Name field is required.',

                'regex_match' =>
                    'The First Name must contain letters only.'
            ]
        );


        /*
        |------------------------------------------------------------------
        | Middle Name
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'middle_name',
            'Middle Name',
            "trim|regex_match[/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/]",
            [
                'regex_match' =>
                    'The Middle Name must contain letters only.'
            ]
        );


        /*
        |------------------------------------------------------------------
        | Last Name
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'last_name',
            'Surname',
            "required|trim|regex_match[/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/]",
            [
                'required' =>
                    'The Surname field is required.',

                'regex_match' =>
                    'The Surname must contain letters only.'
            ]
        );
    }


    /**
     * Set student contact validation rules.
     */
    protected function set_contact_rules()
    {
        /*
        |------------------------------------------------------------------
        | Mobile Number
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'mobile_no',
            'Mobile Number',
            'trim|numeric|exact_length[11]',
            [
                'numeric' =>
                    'The Mobile Number must contain numbers only.',

                'exact_length' =>
                    'The Mobile Number must contain exactly 11 digits.'
            ]
        );


        /*
        |------------------------------------------------------------------
        | Email
        |------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'email',
            'Email',
            'trim|valid_email',
            [
                'valid_email' =>
                    'Please enter a valid email address.'
            ]
        );
    }


    /**
     * Set enrollment validation rules.
     */
    // protected function set_enrollment_rules()
    // {
    //     /*
    //     |------------------------------------------------------------------
    //     | Academic Year
    //     |------------------------------------------------------------------
    //     */

    //     $this->CI->form_validation->set_rules(
    //         'academic_year',
    //         'Academic Year',
    //         'required|trim',
    //         [
    //             'required' =>
    //                 'Please select an Academic Year.'
    //         ]
    //     );


    //     /*
    //     |------------------------------------------------------------------
    //     | Grade Level
    //     |------------------------------------------------------------------
    //     */

    //     $this->CI->form_validation->set_rules(
    //         'grade_level',
    //         'Grade Level',
    //         'required|trim',
    //         [
    //             'required' =>
    //                 'Please select a Grade Level.'
    //         ]
    //     );


    //     /*
    //     |------------------------------------------------------------------
    //     | Section
    //     |------------------------------------------------------------------
    //     */

    //     $this->CI->form_validation->set_rules(
    //         'section',
    //         'Section',
    //         'required|trim',
    //         [
    //             'required' =>
    //                 'Please select a Section.'
    //         ]
    //     );


    //     /*
    //     |------------------------------------------------------------------
    //     | Admission Type
    //     |------------------------------------------------------------------
    //     */

    //     $this->CI->form_validation->set_rules(
    //         'admission_type',
    //         'Admission Type',
    //         'required|trim',
    //         [
    //             'required' =>
    //                 'Please select an Admission Type.'
    //         ]
    //     );
    // }

    /**
     * Set student address validation rules.
     */
    protected function set_address_rules()
    {
        /*
        |--------------------------------------------------------------------------
        | Current Address
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'current_house_no',
            'Current House / Building No.',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'current_street',
            'Current Street',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'current_barangay',
            'Current Barangay',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'current_city',
            'Current City / Municipality',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'current_province',
            'Current Province',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'current_postal_code',
            'Current Postal Code',
            'trim|numeric',
            [
                'numeric' =>
                    'The Current Postal Code must contain numbers only.'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Permanent Address
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'permanent_house_no',
            'Permanent House / Building No.',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'permanent_street',
            'Permanent Street',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'permanent_barangay',
            'Permanent Barangay',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'permanent_city',
            'Permanent City / Municipality',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'permanent_province',
            'Permanent Province',
            'trim'
        );

        $this->CI->form_validation->set_rules(
            'permanent_postal_code',
            'Permanent Postal Code',
            'trim|numeric',
            [
                'numeric' =>
                    'The Permanent Postal Code must contain numbers only.'
            ]
        );
    }

    /**
     * Validate submitted guardians.
     *
     * @param mixed $guardians
     * @return array
     */
    protected function validate_guardians($guardians)
    {
        $errors = [];


        if (
            empty($guardians)
            ||
            !is_array($guardians)
        ) {
            return $errors;
        }


        /*
        |--------------------------------------------------------------------------
        | Allowed Guardian Types
        |--------------------------------------------------------------------------
        */

        $allowed_types = [
            'father',
            'mother',
            'guardian'
        ];


        /*
        |--------------------------------------------------------------------------
        | Primary Guardian
        |--------------------------------------------------------------------------
        */

        $primary_count = 0;


        foreach ($guardians as $guardian) {

            if (!empty($guardian['is_primary'])) {
                $primary_count++;
            }
        }


        if ($primary_count > 1) {

            $errors['_global']['is_primary'] =
                'Only one guardian can be the primary contact.';
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Each Guardian
        |--------------------------------------------------------------------------
        */

        foreach (
            $guardians as $index => $guardian
        ) {

            /*
            |----------------------------------------------------------------------
            | Guardian Type
            |----------------------------------------------------------------------
            */

            $guardian_type =
                trim(
                    $guardian['guardian_type'] ?? ''
                );


            if ($guardian_type === '') {

                $errors[$index]['guardian_type'] =
                    'Guardian Type is required.';

            } elseif (
                !in_array(
                    $guardian_type,
                    $allowed_types,
                    true
                )
            ) {

                $errors[$index]['guardian_type'] =
                    'Invalid Guardian Type.';
            }


            /*
            |----------------------------------------------------------------------
            | Relationship
            |----------------------------------------------------------------------
            */

            $relationship =
                trim(
                    $guardian['relationship'] ?? ''
                );


            if ($relationship === '') {

                $errors[$index]['relationship'] =
                    'Relationship is required.';
            }


            /*
            |----------------------------------------------------------------------
            | First Name
            |----------------------------------------------------------------------
            */

            $first_name =
                trim(
                    $guardian['first_name'] ?? ''
                );


            if ($first_name === '') {

                $errors[$index]['first_name'] =
                    'First Name is required.';

            } elseif (
                !preg_match(
                    "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/u",
                    $first_name
                )
            ) {

                $errors[$index]['first_name'] =
                    'First Name must contain letters only.';
            }


            /*
            |----------------------------------------------------------------------
            | Middle Name
            |----------------------------------------------------------------------
            */

            $middle_name =
                trim(
                    $guardian['middle_name'] ?? ''
                );


            if (
                $middle_name !== ''
                &&
                !preg_match(
                    "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/u",
                    $middle_name
                )
            ) {

                $errors[$index]['middle_name'] =
                    'Middle Name must contain letters only.';
            }


            /*
            |----------------------------------------------------------------------
            | Last Name
            |----------------------------------------------------------------------
            */

            $last_name =
                trim(
                    $guardian['last_name'] ?? ''
                );


            if ($last_name === '') {

                $errors[$index]['last_name'] =
                    'Last Name is required.';

            } elseif (
                !preg_match(
                    "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/u",
                    $last_name
                )
            ) {

                $errors[$index]['last_name'] =
                    'Last Name must contain letters only.';
            }


            /*
            |----------------------------------------------------------------------
            | Mobile Number
            |----------------------------------------------------------------------
            */

            $mobile =
                trim(
                    $guardian['mobile_no'] ?? ''
                );


            if ($mobile === '') {

                $errors[$index]['mobile_no'] =
                    'Mobile Number is required.';

            } elseif (
                !preg_match(
                    '/^[0-9]{11}$/',
                    $mobile
                )
            ) {

                $errors[$index]['mobile_no'] =
                    'Mobile Number must contain exactly 11 digits.';
            }


            /*
            |----------------------------------------------------------------------
            | Email
            |----------------------------------------------------------------------
            */

            $email =
                trim(
                    $guardian['email'] ?? ''
                );


            if ($email === '') {

                $errors[$index]['email'] =
                    'Email address is required.';

            }else if (
                $email !== ''
                &&
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $errors[$index]['email'] =
                    'Please enter a valid email address.';
            }
        }


        return $errors;
    }


    /**
     * Get all validation errors.
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }
}