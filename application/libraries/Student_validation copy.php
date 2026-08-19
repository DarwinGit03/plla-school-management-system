<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Student_validation
{
    protected $CI;


    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->library(
            'form_validation'
        );
    }


    /**
     * Set student registration validation rules.
     */
    public function set_student_rules()
    {
        /*
        |--------------------------------------------------------------------------
        | LRN
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | Student Number
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | First Name
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | Middle Name
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | Last Name
        |--------------------------------------------------------------------------
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


        /*
        |--------------------------------------------------------------------------
        | Mobile Number
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'mobile_no',
            'Mobile Number',
            'trim|numeric|exact_length[10]',
            [
                'numeric' =>
                    'The Mobile Number must contain numbers only.',

                'exact_length' =>
                    'The Mobile Number must contain exactly 10 digits.'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
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


        /*
        |--------------------------------------------------------------------------
        | Academic Year
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'academic_year',
            'Academic Year',
            'required|trim',
            [
                'required' =>
                    'Please select an Academic Year.'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Grade Level
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'grade_level',
            'Grade Level',
            'required|trim',
            [
                'required' =>
                    'Please select a Grade Level.'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Section
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'section',
            'Section',
            'required|trim',
            [
                'required' =>
                    'Please select a Section.'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Admission Type
        |--------------------------------------------------------------------------
        */

        $this->CI->form_validation->set_rules(
            'admission_type',
            'Admission Type',
            'required|trim',
            [
                'required' =>
                    'Please select an Admission Type.'
            ]
        );
    }


    /**
     * Validate guardians.
     *
     * @param array $guardians
     * @return bool
     */
    public function validate_guardians($guardians)
    {
        if (!is_array($guardians)) {
            return true;
        }


        foreach ($guardians as $index => $guardian) {

            $number = $index + 1;


            if (
                empty(
                    trim(
                        $guardian['guardian_type'] ?? ''
                    )
                )
            ) {

                return 'Guardian ' .
                    $number .
                    ': Guardian Type is required.';
            }


            if (
                empty(
                    trim(
                        $guardian['relationship'] ?? ''
                    )
                )
            ) {

                return 'Guardian ' .
                    $number .
                    ': Relationship is required.';
            }


            $first_name =
                trim(
                    $guardian['first_name'] ?? ''
                );


            if ($first_name === '') {

                return 'Guardian ' .
                    $number .
                    ': First Name is required.';
            }


            if (
                !preg_match(
                    "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/u",
                    $first_name
                )
            ) {

                return 'Guardian ' .
                    $number .
                    ': First Name must contain letters only.';
            }


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

                return 'Guardian ' .
                    $number .
                    ': Middle Name must contain letters only.';
            }


            $last_name =
                trim(
                    $guardian['last_name'] ?? ''
                );


            if ($last_name === '') {

                return 'Guardian ' .
                    $number .
                    ': Last Name is required.';
            }


            if (
                !preg_match(
                    "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/u",
                    $last_name
                )
            ) {

                return 'Guardian ' .
                    $number .
                    ': Last Name must contain letters only.';
            }


            $mobile =
                trim(
                    $guardian['mobile_no'] ?? ''
                );


            if ($mobile === '') {

                return 'Guardian ' .
                    $number .
                    ': Mobile Number is required.';
            }


            if (
                !preg_match(
                    '/^[0-9]{11}$/',
                    $mobile
                )
            ) {

                return 'Guardian ' .
                    $number .
                    ': Mobile Number must contain exactly 11 digits.';
            }


        }


        return true;
    }

}