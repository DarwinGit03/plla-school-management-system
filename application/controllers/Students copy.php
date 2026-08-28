<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();
        /*
        |--------------------------------------------------------------------------
        | Student Service
        |--------------------------------------------------------------------------
        */

        $this->load->library('form_validation');

        $this->load->library('Student_service');
        $this->load->library('student_validation');

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $this->load->library('pagination');

    }

    
    /**
     * AJAX: Get academic years.
     */
    public function academic_years()
    {
        $years =
            $this->student_service
                ->get_academic_years();


        $this->output
            ->set_content_type('application/json')
            ->set_output(
                json_encode($years)
            );
    }


    /**
     * AJAX: Get grade levels.
     */
    public function grade_levels()
    {
        $year =
            trim(
                $this->input->get(
                    'year',
                    true
                )
            );


        if ($year === '') {

            $this->output
                ->set_status_header(400)
                ->set_content_type(
                    'application/json'
                )
                ->set_output(
                    json_encode([
                        'error' =>
                            'Academic year is required.'
                    ])
                );

            return;
        }


        $grades =
            $this->student_service
                ->get_grade_levels_by_year(
                    $year
                );


        $this->output
            ->set_content_type(
                'application/json'
            )
            ->set_output(
                json_encode($grades)
            );
    }


    /**
     * AJAX: Get sections.
     */
    public function sections()
    {
        $year =
            trim(
                $this->input->get(
                    'year',
                    true
                )
            );


        $grade =
            trim(
                $this->input->get(
                    'grade',
                    true
                )
            );


        if (
            $year === '' ||
            $grade === ''
        ) {

            $this->output
                ->set_status_header(400)
                ->set_content_type(
                    'application/json'
                )
                ->set_output(
                    json_encode([
                        'error' =>
                            'Academic year and grade level are required.'
                    ])
                );

            return;
        }


        $sections =
            $this->student_service
                ->get_sections_by_year_and_grade(
                    $year,
                    $grade
                );


        $this->output
            ->set_content_type(
                'application/json'
            )
            ->set_output(
                json_encode($sections)
            );
    }


    /**
     * Student List
     */
    // public function index()
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Filters
    //     |--------------------------------------------------------------------------
    //     */

    //     $filters = [

    //         'search' => trim(
    //             $this->input->get('search', true)
    //         ),

    //         'status' => trim(
    //             $this->input->get('status', true)
    //         ),

    //         'academic_year' => trim(
    //             $this->input->get('academic_year', true)
    //         ),

    //         'grade_level' => trim(
    //             $this->input->get('grade_level', true)
    //         ),

    //         'section' => trim(
    //             $this->input->get('section', true)
    //         )

    //     ];


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Pagination
    //     |--------------------------------------------------------------------------
    //     */

    //     $per_page = 10;

    //     $page = (int) $this->input->get('page');

    //     $page = max($page, 1);

    //     $offset = ($page - 1) * $per_page;


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Get Students
    //     |--------------------------------------------------------------------------
    //     */

    //     $students = $this->student_service->get_students(
    //         $filters,
    //         $per_page,
    //         $offset
    //     );


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Count
    //     |--------------------------------------------------------------------------
    //     */

    //     $total_students =
    //         $this->student_service->count_students(
    //             $filters
    //         );


    //     /*
    //     |--------------------------------------------------------------------------
    //     | View Data
    //     |--------------------------------------------------------------------------
    //     */

    //     $data = [

    //         'title' => 'Students',

    //         'page_title' => 'Students',

    //         'page_subtitle' =>
    //             'Manage student information',

    //         'breadcrumb' => [
    //             'Students'
    //         ],

    //         'students' => $students,

    //         'total_students' => $total_students,

    //         'filters' => $filters,

    //         'per_page' => $per_page,

    //         'current_page' => $page

    //     ];


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Dashboard Layout
    //     |--------------------------------------------------------------------------
    //     */

    //     $data['content'] =
    //         'students/index';

    //     $this->load->view(
    //         'dashboard/layouts/master',
    //         $data
    //     );
    // }

    /**
     * Student list.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = [

            'search' => trim(
                $this->input->get('search', true)
            ),

            'status' => trim(
                $this->input->get('status', true)
            ),

            'academic_year' => trim(
                $this->input->get('academic_year', true)
            ),

            'grade_level' => trim(
                $this->input->get('grade_level', true)
            ),

            'section' => trim(
                $this->input->get('section', true)
            )

        ];


        /* dynamic dropdown
        |--------------------------------------------------------------------------
        | Academic Years
        |--------------------------------------------------------------------------
        */

        $academic_years =
            $this->student_service
                ->get_academic_years();


        /*
        |--------------------------------------------------------------------------
        | Grade Levels
        |--------------------------------------------------------------------------
        */

        $grade_levels = [];

        if (!empty($filters['academic_year'])) {

            $grade_levels =
                $this->student_service
                    ->get_grade_levels_by_year(
                        $filters['academic_year']
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Sections
        |--------------------------------------------------------------------------
        */

        $sections = [];

        if (
            !empty($filters['academic_year'])
            &&
            !empty($filters['grade_level'])
        ) {

            $sections =
                $this->student_service
                    ->get_sections_by_year_and_grade(
                        $filters['academic_year'],
                        $filters['grade_level']
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $per_page = 10;

        $page = (int) $this->input->get('page');

        $page = max(
            $page,
            1
        );


        /*
        |--------------------------------------------------------------------------
        | Count Students
        |--------------------------------------------------------------------------
        */

        $total_students =
            $this->student_service
                ->count_students(
                    $filters
                );


        /*
        |--------------------------------------------------------------------------
        | Calculate Total Pages
        |--------------------------------------------------------------------------
        */

        $total_pages = (int) ceil(
            $total_students / $per_page
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Invalid Page
        |--------------------------------------------------------------------------
        */

        if (
            $total_pages > 0
            &&
            $page > $total_pages
        ) {

            $page = $total_pages;
        }


        /*
        |--------------------------------------------------------------------------
        | Offset
        |--------------------------------------------------------------------------
        */

        $offset =
            ($page - 1) * $per_page;


        /*
        |--------------------------------------------------------------------------
        | Get Students
        |--------------------------------------------------------------------------
        */

        $students =
            $this->student_service
                ->get_students(
                    $filters,
                    $per_page,
                    $offset
                );


        /*
        |--------------------------------------------------------------------------
        | View Data
        |--------------------------------------------------------------------------
        */

        $data = [

            'title' => 'Students',

            'page_title' => 'Students',

            'page_subtitle' =>
                'Manage student information',

            'breadcrumb' => [
                'Students'
            ],

            'students' =>
                $students,

            'total_students' =>
                $total_students,

            'total_pages' =>
                $total_pages,

            'filters' =>
                $filters,

            'per_page' =>
                $per_page,

            'current_page' =>
                $page,

            'academic_years' =>
                $this->student_service
                    ->get_academic_years()

        ];


        /*
        |--------------------------------------------------------------------------
        | Dashboard Layout
        |--------------------------------------------------------------------------
        */

        $data['content'] =
            'students/index';

        $this->load->view(
            'dashboard/layouts/master',
            $data
        );
    }
    
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Handle Form Submission
        |--------------------------------------------------------------------------
        */

        if ($this->input->method() === 'post') {

            /*
            |--------------------------------------------------------------------------
            | Get Guardians
            |--------------------------------------------------------------------------
            */

            $guardians = $this->input->post('guardians');

            if (!is_array($guardians)) {
                $guardians = [];
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Registration
            |--------------------------------------------------------------------------
            */

            if (
                !$this->student_validation
                    ->validate_registration($guardians)
            ) {

                $validation_errors =
                    $this->student_validation
                        ->get_errors();


                $show_lrn_duplicate_modal =
                    false;


                /*
                |--------------------------------------------------------------------------
                | Detect LRN Duplicate
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($validation_errors['student'])
                    &&
                    stripos(
                        $validation_errors['student'],
                        'LRN'
                    ) !== false
                    &&
                    stripos(
                        $validation_errors['student'],
                        'already'
                    ) !== false
                ) {

                    $show_lrn_duplicate_modal =
                        true;
                }


                return $this->show_create_form(
                    [
                        'guardians' =>
                            $guardians,

                        'validation_errors' =>
                            $validation_errors,

                        'show_lrn_duplicate_modal' =>
                            $show_lrn_duplicate_modal
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Student Data
            |--------------------------------------------------------------------------
            */

            $student_data = [

                'lrn' =>
                    trim(
                        $this->input->post('lrn')
                    ),

                'student_no' =>
                    trim(
                        $this->input->post('student_no')
                    ),

                'first_name' =>
                    trim(
                        $this->input->post('first_name')
                    ),

                'middle_name' =>
                    trim(
                        $this->input->post('middle_name')
                    ),

                'last_name' =>
                    trim(
                        $this->input->post('last_name')
                    ),

                'suffix' =>
                    trim(
                        $this->input->post('suffix')
                    ),

                'birth_date' =>
                    $this->input->post(
                        'birth_date'
                    ),

                'birth_place' =>
                    $this->input->post(
                        'birth_place'
                    ),

                'gender' =>
                    $this->input->post(
                        'gender'
                    ),

                'nationality' =>
                    $this->input->post(
                        'nationality'
                    ),

                'mobile_no' =>
                    $this->input->post(
                        'mobile_no'
                    ),

                'email' =>
                    $this->input->post(
                        'email'
                    )
            ];


            /*
            |--------------------------------------------------------------------------
            | Current Address
            |--------------------------------------------------------------------------
            */

            $current_address = [
                
                'lrn' =>
                    trim(
                        $this->input->post('lrn')
                    ),

                'address_type' =>
                    'current',

                'house_no' =>
                    trim(
                        $this->input->post(
                            'current_house_no'
                        )
                    ),

                'street' =>
                    trim(
                        $this->input->post(
                            'current_street'
                        )
                    ),

                'barangay' =>
                    trim(
                        $this->input->post(
                            'current_barangay'
                        )
                    ),

                'city' =>
                    trim(
                        $this->input->post(
                            'current_city'
                        )
                    ),

                'province' =>
                    trim(
                        $this->input->post(
                            'current_province'
                        )
                    ),

                'postal_code' =>
                    trim(
                        $this->input->post(
                            'current_postal_code'
                        )
                    )
            ];


            /*
            |--------------------------------------------------------------------------
            | Permanent Address
            |--------------------------------------------------------------------------
            */

            // $permanent_address = [

            //     'address_type' =>
            //         'permanent',

            //     'house_no' =>
            //         trim(
            //             $this->input->post(
            //                 'permanent_house_no'
            //             )
            //         ),

            //     'street' =>
            //         trim(
            //             $this->input->post(
            //                 'permanent_street'
            //             )
            //         ),

            //     'barangay' =>
            //         trim(
            //             $this->input->post(
            //                 'permanent_barangay'
            //             )
            //         ),

            //     'city' =>
            //         trim(
            //             $this->input->post(
            //                 'permanent_city'
            //             )
            //         ),

            //     'province' =>
            //         trim(
            //             $this->input->post(
            //                 'permanent_province'
            //             )
            //         ),

            //     'postal_code' =>
            //         trim(
            //             $this->input->post(
            //                 'permanent_postal_code'
            //             )
            //         )
            // ];

            $permanent_same_as_current =
            $this->input->post('permanent_same_as_current');


            if ($permanent_same_as_current === '1') {
                $permanent_address = [
                    'lrn' =>
                        trim(
                            $this->input->post('lrn')
                        ),
                    'address_type' =>
                        'permanent',

                    'house_no' =>
                        trim(
                            $this->input->post(
                                'current_house_no'
                            )
                        ),

                    'street' =>
                        trim(
                            $this->input->post(
                                'current_street'
                            )
                        ),

                    'barangay' =>
                        trim(
                            $this->input->post(
                                'current_barangay'
                            )
                        ),

                    'city' =>
                        trim(
                            $this->input->post(
                                'current_city'
                            )
                        ),

                    'province' =>
                        trim(
                            $this->input->post(
                                'current_province'
                            )
                        ),

                    'postal_code' =>
                        trim(
                            $this->input->post(
                                'current_postal_code'
                            )
                        )
                ];

            } else {
                $permanent_address = [
                    
                    'lrn' =>
                        trim(
                            $this->input->post('lrn')
                        ),
                    'address_type' =>
                        'permanent',

                    'house_no' =>
                        trim(
                            $this->input->post(
                                'permanent_house_no'
                            )
                        ),

                    'street' =>
                        trim(
                            $this->input->post(
                                'permanent_street'
                            )
                        ),

                    'barangay' =>
                        trim(
                            $this->input->post(
                                'permanent_barangay'
                            )
                        ),

                    'city' =>
                        trim(
                            $this->input->post(
                                'permanent_city'
                            )
                        ),

                    'province' =>
                        trim(
                            $this->input->post(
                                'permanent_province'
                            )
                        ),

                    'postal_code' =>
                        trim(
                            $this->input->post(
                                'permanent_postal_code'
                            )
                        )
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Enrollment
            |--------------------------------------------------------------------------
            */

            $enrollment_data = [
                
                'lrn' =>
                    trim(
                        $this->input->post('lrn')
                    ),

                'academic_year' =>
                    $this->input->post(
                        'academic_year'
                    ),

                'grade_level' =>
                    $this->input->post(
                        'grade_level'
                    ),

                'section' =>
                    $this->input->post(
                        'section'
                    ),

                'admission_type' =>
                    $this->input->post(
                        'admission_type'
                    )
            ];


            /*
            |--------------------------------------------------------------------------
            | Create Student
            |--------------------------------------------------------------------------
            */

            $student_id =
                $this->student_service
                    ->create_student(
                        $student_data,
                        $enrollment_data,
                        $guardians,
                        [
                            $current_address,
                            $permanent_address
                        ]
                    );


            /*
            |--------------------------------------------------------------------------
            | Creation Failed
            |--------------------------------------------------------------------------
            */

            if (!$student_id) {

                $this->session->set_flashdata(
                    'error',
                    'Unable to register the student. Please try again.'
                );

                return $this->show_create_form([
                    'guardians' => $guardians,
                    'validation_errors' => [
                        'database' =>
                            'Student creation failed.'
                    ]
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Creation Successful
            |--------------------------------------------------------------------------
            */

            $this->session->set_flashdata(
                'success',
                'Student successfully registered.'
            );

            return redirect(
                'students/view/' . $student_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Display Registration Form
        |--------------------------------------------------------------------------
        */

        return $this->show_create_form();
    }

    /**
     * Display the student registration form.
     *
     * @param array $extra
     * @return void
     */
    private function show_create_form(
    array $extra = []
    ) {
        $data = array_merge(

            [
                'title' =>
                    'Register Student',

                'page_title' =>
                    'Register Student',

                'page_subtitle' =>
                    'Create a new student record',

                'breadcrumb' => [
                    'Students',
                    'Register'
                ],

                'content' =>
                    'students/create',

                'guardians' =>
                    []
            ],

            $extra

        );


        $this->load->view(
            'dashboard/layouts/master',
            $data
        );
    }

    /**
     * Student Profile
     *
     * @param int $id
     */
    public function view($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate ID
        |--------------------------------------------------------------------------
        */

        $id = (int) $id;

        if ($id <= 0) {

            show_404();
        }


        /*
        |--------------------------------------------------------------------------
        | Get Student
        |--------------------------------------------------------------------------
        */

        $student =
            $this->student_service
                ->get_student_profile($id);

        $guardians =
            $this->student_service
                ->get_guardians($id);


        $addresses =
            $this->student_service
                ->get_addresses($id);


        /*
        |--------------------------------------------------------------------------
        | Student Not Found
        |--------------------------------------------------------------------------
        */

        if (!$student) {

            show_404();
        }


        /*
        |--------------------------------------------------------------------------
        | View Data
        |--------------------------------------------------------------------------
        */

        $data = [

            'title' =>
                'Student Profile',

            'page_title' =>
                'Student Profile',

            'page_subtitle' =>
                'View student information',

            'breadcrumb' => [

                'Students',

                $student->last_name .
                ', ' .
                $student->first_name

            ],

            'student' => $student,
           
            'guardians' => $guardians,

            'addresses' => $addresses,

        ];


        /*
        |--------------------------------------------------------------------------
        | Dashboard Layout
        |--------------------------------------------------------------------------
        */

        $data['content'] =
            'students/view';


        $this->load->view(
            'dashboard/layouts/master',
            $data
        );
    }

    /**
     * Check LRN for duplicate registration.
     *
     * AJAX endpoint.
     */
    public function check_lrn()
    {
        /*
        |--------------------------------------------------------------------------
        | Only allow POST
        |--------------------------------------------------------------------------
        */

        if (
            $this->input->method() !== 'post'
        ) {

            show_404();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Get LRN
        |--------------------------------------------------------------------------
        */

        $lrn =
            trim(
                $this->input->post('lrn')
            );


        /*
        |--------------------------------------------------------------------------
        | Empty LRN
        |--------------------------------------------------------------------------
        */

        if ($lrn === '') {

            $this->output
                ->set_content_type(
                    'application/json'
                )
                ->set_output(
                    json_encode([
                        'success' => false,
                        'exists' => false
                    ])
                );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Student
        |--------------------------------------------------------------------------
        */

        $student =
            $this->student_service
                ->get_student_by_lrn(
                    $lrn
                );


        /*
        |--------------------------------------------------------------------------
        | LRN Found
        |--------------------------------------------------------------------------
        */

        if ($student) {

            $this->output
                ->set_content_type(
                    'application/json'
                )
                ->set_output(
                    json_encode([

                        'success' => true,

                        'exists' => true,

                        'student' => [

                            'name' =>
                                trim(
                                    $student->first_name .
                                    ' ' .
                                    $student->middle_name .
                                    ' ' .
                                    $student->last_name
                                ),

                            'academic_year' =>
                                $student->academic_year,

                            'grade_level' =>
                                $student->grade_level,

                            'section' =>
                                $student->section

                        ]

                    ])
                );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | LRN Not Found
        |--------------------------------------------------------------------------
        */

        $this->output
            ->set_content_type(
                'application/json'
            )
            ->set_output(
                json_encode([

                    'success' => true,

                    'exists' => false

                ])
            );
    }

    /**
     * Validate that the LRN is not already registered.
     *
     * @param string $lrn
     * @return bool
     */
    public function lrn_unique($lrn)
    {
        $lrn = trim($lrn);

        /*
        |--------------------------------------------------------------------------
        | Empty LRN
        |--------------------------------------------------------------------------
        */

        if ($lrn === '') {

            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Existing Student
        |--------------------------------------------------------------------------
        */

        $student =
            $this->student_service
                ->get_student_by_lrn($lrn);


        /*
        |--------------------------------------------------------------------------
        | Duplicate
        |--------------------------------------------------------------------------
        */

        if ($student) {

            $this->form_validation->set_message(    
                'lrn_unique',
                'This LRN is already registered.'
            );

            return false;
        }

        log_message(
            'debug',
            '[LRN CHECK] LRN available: ' . $lrn
        );


        /*
        |--------------------------------------------------------------------------
        | Available
        |--------------------------------------------------------------------------
        */

        return true;
    }

}