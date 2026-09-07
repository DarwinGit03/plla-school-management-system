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
         * Student list.
         */
        public function index()
        {
            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */
            $status =
                $this->input->get(
                    'status',
                    true
                );

            if ($status === null) {
                $status = 'active';
            }

            $filters = [

                'search' => trim(
                    $this->input->get(
                        'search',
                        true
                    )
                ),

                'status' => trim($status),

            // $filters = [

            //     'search' => trim(
            //         $this->input->get('search', true)
            //     ),

            //     'status' => trim(
            //         $this->input->get('status', true)
            //     ),

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
                    'Student List'
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

        
        public function edit($id)
        {

            /*
            |--------------------------------------------------------------------------
            | Determine LRN Permission
            |--------------------------------------------------------------------------
            */

            // $role_id = (int) $this->session->userdata('role_id');
            // $is_admin = in_array($role_id, [3, 2], true);

            /*
            |--------------------------------------------------------------------------
            | Get Student
            |--------------------------------------------------------------------------
            */

            $student =
                $this->student_service
                    ->get_student_profile($id);


            /*
            |--------------------------------------------------------------------------
            | Student Not Found
            |--------------------------------------------------------------------------
            */

            if (!$student) {

                show_404();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Get Existing Guardians
            |--------------------------------------------------------------------------
            */

            $guardians =
                $this->student_service
                    ->get_guardians($id);


            /*
            |--------------------------------------------------------------------------
            | Get Existing Addresses
            |--------------------------------------------------------------------------
            */

            $addresses =
                $this->student_service
                    ->get_addresses($id);


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

                $submitted_guardians =
                    $this->input->post('guardians');


                if (!is_array($submitted_guardians)) {

                    $submitted_guardians = [];
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Registration Data
                |--------------------------------------------------------------------------
                */

                if (
                    !$this->student_validation
                        ->validate_registration(
                            $submitted_guardians
                        )
                ) {

                    $validation_errors =
                        $this->student_validation
                            ->get_errors();


                    return $this->show_edit_form(
                        $student,
                        $submitted_guardians,
                        $addresses,
                        [
                            'validation_errors' =>
                                $validation_errors
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
                        trim(
                            $this->input->post(
                                'birth_place'
                            )
                        ),

                    'gender' =>
                        $this->input->post(
                            'gender'
                        ),

                    'nationality' =>
                        trim(
                            $this->input->post(
                                'nationality'
                            )
                        ),

                    'mobile_no' =>
                        trim(
                            $this->input->post(
                                'mobile_no'
                            )
                        ),

                    'email' =>
                        trim(
                            $this->input->post(
                                'email'
                            )
                        )
                ];

                /*
                |--------------------------------------------------------------------------
                | Admin-only fields
                |--------------------------------------------------------------------------
                */

                // if ($this->is_admin()) {

                //     $student_data['lrn'] =
                //         trim(
                //             $this->input->post('lrn')
                //         );

                //     $student_data['student_no'] =
                //         trim(
                //             $this->input->post('student_no')
                //         );
                // }


                /*
                |--------------------------------------------------------------------------
                | Current Address
                |--------------------------------------------------------------------------
                */

                $current_address = [

                    'address_type' =>
                        'current',
                        
                    'lrn' =>
                        trim(
                            $this->input->post('lrn')
                        ),

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

                $permanent_same_as_current =
                    $this->input->post(
                        'permanent_same_as_current'
                    );


                if (
                    $permanent_same_as_current === '1'
                ) {

                    $permanent_address = [

                        'address_type' =>
                            'permanent',
                        
                        'lrn' =>
                            trim(
                                $this->input->post('lrn')
                            ),

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

                        'address_type' =>
                            'permanent',
                        
                        'lrn' =>
                            trim(
                                $this->input->post('lrn')
                            ),

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
                | Preserve Address IDs
                |--------------------------------------------------------------------------
                |
                | The service needs the existing address ID in order to UPDATE
                | instead of creating a new address.
                |
                */

                foreach ($addresses as $address) {

                    if (
                        $address->address_type === 'current'
                    ) {

                        $current_address['id'] =
                            $address->id;

                    }

                    if (
                        $address->address_type === 'permanent'
                    ) {

                        $permanent_address['id'] =
                            $address->id;

                    }
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
                | Update Student
                |--------------------------------------------------------------------------
                */

                $updated =
                    $this->student_service
                        ->update_student(

                            $id,

                            $student_data,

                            $enrollment_data,

                            $submitted_guardians,

                            [
                                $current_address,
                                $permanent_address
                            ]
                        );


                /*
                |--------------------------------------------------------------------------
                | Update Failed
                |--------------------------------------------------------------------------
                */

                if (!$updated) {

                    return $this->show_edit_form(
                        $student,
                        $submitted_guardians,
                        [
                            (object) $current_address,
                            (object) $permanent_address
                        ],
                        [
                            'database_error' =>
                                'Unable to update the student. Please try again.'
                        ]
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Success
                |--------------------------------------------------------------------------
                */

                $this->session->set_flashdata(
                    'success',
                    'Student information successfully updated.'
                );


                return redirect(
                    'students/view/' . $id
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Display Edit Form
            |--------------------------------------------------------------------------
            */

            return $this->show_edit_form(
                $student,
                $guardians,
                $addresses
            );
        }

        /**
         * Display the student edit form.
         *
         * @param object $student
         * @param array $guardians
         * @param array $addresses
         * @param array $extra
         * @return void
         */
        private function show_edit_form(
            $student,
            array $guardians = [],
            array $addresses = [],
            array $extra = []
        ) {
            /*
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


            if (
                !empty(
                    $student->academic_year
                )
            ) {

                $grade_levels =
                    $this->student_service
                        ->get_grade_levels_by_year(
                            $student->academic_year
                        );
            }


            /*
            |--------------------------------------------------------------------------
            | Sections
            |--------------------------------------------------------------------------
            */

            $sections = [];


            if (
                !empty(
                    $student->academic_year
                )
                &&
                !empty(
                    $student->grade_level
                )
            ) {

                $sections =
                    $this->student_service
                        ->get_sections_by_year_and_grade(

                            $student->academic_year,

                            $student->grade_level
                        );
            }


            /*
            |--------------------------------------------------------------------------
            | View Data
            |--------------------------------------------------------------------------
            */

            $data = array_merge(

                [

                    'title' =>
                        'Edit Student',

                    'page_title' =>
                        'Edit Student',

                    'page_subtitle' =>
                        'Update student information',

                    'breadcrumb' => [

                        'Students',

                        'Edit Student'

                    ],

                    'content' =>
                        'students/edit',

                    'student' =>
                        $student,

                    'guardians' =>
                        $guardians,

                    'addresses' =>
                        $addresses,

                    'academic_years' =>
                        $academic_years,

                    'grade_levels' =>
                        $grade_levels,

                    'sections' =>
                        $sections

                ],

                $extra

            );


            /*
            |--------------------------------------------------------------------------
            | Load View
            |--------------------------------------------------------------------------
            */

            $this->load->view(
                'dashboard/layouts/master',
                $data
            );
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
                        'Enrollment'
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

        public function view($id)
        {
            /*
            |--------------------------------------------------------------------------
            | Get Student
            |--------------------------------------------------------------------------
            */

            $student =
                $this->student_service
                    ->get_student_profile($id);


            /*
            |--------------------------------------------------------------------------
            | Student Not Found
            |--------------------------------------------------------------------------
            */

            if (!$student) {

                show_404();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Get Guardians
            |--------------------------------------------------------------------------
            */

            $guardians =
                $this->student_service
                    ->get_guardians($id);


            /*
            |--------------------------------------------------------------------------
            | Get Addresses
            |--------------------------------------------------------------------------
            */

            $addresses =
                $this->student_service
                    ->get_addresses($id);

            /*
            |--------------------------------------------------------------------------
            | Enrollment
            |--------------------------------------------------------------------------
            */

            $enrollment_history =
                $this->student_service
                    ->get_enrollment_history($id);


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
                    'View complete student information.',

                'breadcrumb' => [
                    'Students',
                    'Student Profile'
                ],

                'student' =>
                    $student,

                'guardians' =>
                    $guardians,

                'addresses' =>
                    $addresses,

                'enrollment_history' =>
                    $enrollment_history
            ];


            /*
            |--------------------------------------------------------------------------
            | Load View
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
         * Works for both CREATE and EDIT.
         *
         * @param string $lrn
         * @return bool
         */
        public function lrn_unique($lrn)
        {
            $lrn =
                trim($lrn);


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
                    ->get_student_by_lrn(
                        $lrn
                    );


            /*
            |--------------------------------------------------------------------------
            | No Existing Student
            |--------------------------------------------------------------------------
            */

            if (!$student) {

                return true;
            }


            /*
            |--------------------------------------------------------------------------
            | Detect Current Student During Edit
            |--------------------------------------------------------------------------
            */

            $current_student_id =
                (int) $this->input->post(
                    'student_id'
                );


            $existing_student_id =
                (int) $student->id;


            /*
            |--------------------------------------------------------------------------
            | Same Student
            |--------------------------------------------------------------------------
            |
            | The LRN belongs to the student currently being edited.
            |
            */

            if (
                $current_student_id > 0
                &&
                $current_student_id === $existing_student_id
            ) {

                return true;
            }


            /*
            |--------------------------------------------------------------------------
            | Duplicate
            |--------------------------------------------------------------------------
            */

            $this->form_validation->set_message(
                'lrn_unique',
                'This LRN is already registered.'
            );


            return false;
        }

        public function change_status($id)
        {
            if (
                $this->input->method() !== 'post'
            ) {
                show_404();
            }

            if (!is_numeric($id)) {
                show_404();
            }

            $new_status =
                $this->input->post(
                    'new_status',
                    true
                );

            $reason =
                $this->input->post(
                    'reason',
                    true
                );

            $result =
                $this->student_service
                    ->change_status(
                        (int) $id,
                        $new_status,
                        $reason
                    );

            if ($result['status']) {

                $this->session->set_flashdata(
                    'success',
                    $result['message']
                );

            } else {

                $this->session->set_flashdata(
                    'error',
                    $result['message']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Preserve Current Student List Filters
            |--------------------------------------------------------------------------
            */

            $redirect_params = [

                'search' =>
                    $this->input->post(
                        'filter_search',
                        true
                    ),

                'status' =>
                    $this->input->post(
                        'filter_status',
                        true
                    ),

                'academic_year' =>
                    $this->input->post(
                        'filter_academic_year',
                        true
                    ),

                'grade_level' =>
                    $this->input->post(
                        'filter_grade_level',
                        true
                    ),

                'section' =>
                    $this->input->post(
                        'filter_section',
                        true
                    ),

                'page' =>
                    $this->input->post(
                        'filter_page',
                        true
                    )
            ];

            $redirect_params =
                array_filter(
                    $redirect_params,
                    function ($value) {
                        return $value !== '';
                    }
                );

            $redirect_url =
                'students';

            if (!empty($redirect_params)) {

                $redirect_url .=
                    '?' .
                    http_build_query(
                        $redirect_params
                    );
            }

            redirect($redirect_url);
        }
        // public function change_status($id)
        // {
        //     if (
        //         $this->input->method() !== 'post'
        //     ) {
        //         show_404();
        //     }

        //     if (
        //         !is_numeric($id)
        //     ) {
        //         show_404();
        //     }

        //     $new_status =
        //         $this->input
        //             ->post(
        //                 'new_status',
        //                 true
        //             );

        //     $reason =
        //         $this->input
        //             ->post(
        //                 'reason',
        //                 true
        //             );

        //     $result =
        //         $this->student_service
        //             ->change_status(
        //                 (int) $id,
        //                 $new_status,
        //                 $reason
        //             );

        //     if ($result['status']) {

        //         $this->session->set_flashdata(
        //             'success',
        //             $result['message']
        //         );

        //     } else {

        //         $this->session->set_flashdata(
        //             'error',
        //             $result['message']
        //         );
        //     }

        //     redirect(
        //         'students'
        //     );
        // }

    }