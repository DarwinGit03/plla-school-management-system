<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_model extends CI_Model
{
    protected $table = 'students';

     /* Functions
        et_students()
        count_students()
        get_student()
        create_student()
        create_enrollment()
     */
    public function get_students($filters = [], $limit = null, $offset = null)
    {
        $this->db
            ->select('
                students.id,
                students.student_no,
                students.lrn,
                students.first_name,
                students.middle_name,
                students.last_name,
                students.suffix,
                students.gender,
                students.birth_date,
                students.status,
                student_enrollments.academic_year,
                student_enrollments.grade_level,
                student_enrollments.section
            ')
            ->from($this->table)
            ->join(
                'student_enrollments',
                'student_enrollments.student_id = students.id
                 AND student_enrollments.status = "active"',
                'left'
            )
            ->where('students.deleted_at IS NULL', null, false);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {

            $search = trim($filters['search']);

            $this->db->group_start();

            $this->db
                ->like('students.student_no', $search)
                ->or_like('students.lrn', $search)
                ->or_like('students.first_name', $search)
                ->or_like('students.middle_name', $search)
                ->or_like('students.last_name', $search);

            $this->db->group_end();
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['status'])) {

            $this->db->where(
                'students.status',
                $filters['status']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Academic Year
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['academic_year'])) {

            $this->db->where(
                'student_enrollments.academic_year',
                $filters['academic_year']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Grade Level
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['grade_level'])) {

            $this->db->where(
                'student_enrollments.grade_level',
                $filters['grade_level']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Section
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['section'])) {

            $this->db->where(
                'student_enrollments.section',
                $filters['section']
            );
        }

        $this->db->order_by(
            'students.last_name',
            'ASC'
        );

        $this->db->order_by(
            'students.first_name',
            'ASC'
        );

        if ($limit !== null) {

            $this->db->limit(
                $limit,
                $offset ?? 0
            );
        }

        return $this->db->get()->result();
    }


    /**
     * Count students.
     *
     * @param array $filters
     * @return int
     */
    public function count_students($filters = [])
    {
        $this->db
            ->from($this->table)
            ->join(
                'student_enrollments',
                'student_enrollments.student_id = students.id
                 AND student_enrollments.status = "active"',
                'left'
            )
            ->where('students.deleted_at IS NULL', null, false);

        if (!empty($filters['search'])) {

            $search = trim($filters['search']);

            $this->db->group_start();

            $this->db
                ->like('students.student_no', $search)
                ->or_like('students.lrn', $search)
                ->or_like('students.first_name', $search)
                ->or_like('students.middle_name', $search)
                ->or_like('students.last_name', $search);

            $this->db->group_end();
        }

        if (!empty($filters['status'])) {

            $this->db->where(
                'students.status',
                $filters['status']
            );
        }

        if (!empty($filters['academic_year'])) {

            $this->db->where(
                'student_enrollments.academic_year',
                $filters['academic_year']
            );
        }

        if (!empty($filters['grade_level'])) {

            $this->db->where(
                'student_enrollments.grade_level',
                $filters['grade_level']
            );
        }

        if (!empty($filters['section'])) {

            $this->db->where(
                'student_enrollments.section',
                $filters['section']
            );
        }

        return $this->db->count_all_results();
    }


    /**
     * Get a single student.
     *
     * @param int $id
     * @return object|null
     */
    public function get_student($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('deleted_at IS NULL', null, false)
            ->get($this->table)
            ->row();
    }

    /**
     * Create a student.
     *
     * @param array $data
     * @return int|false
     */
    public function create_student($data)
    {
        $this->db->insert(
            $this->table,
            $data
        );

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    // public function update_student($id, $data)
    // {
    //     return $this->db
    //         ->where('id', $id)
    //         ->update(
    //             $this->table,
    //             $data
    //         );
    // }

    public function update_student($id, $data)
    {
        $existing =
            $this->db
                ->where('id', $id)
                ->get($this->table)
                ->row();

        if (!$existing) {
            return false;
        }

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

                $data['updated_by'] =
                    $this->session
                        ->userdata('employee_no');
                        
                $data['updated_at'] =
                    date('Y-m-d H:i:s');

                return $this->db
                    ->where('id', $id)
                    ->update(
                        $this->table,
                        $data
                    );
            }
        }

        return true;
    }

    public function change_status(
        $id,
        $new_status,
        $employee_no
    ) {
        return $this->db
            ->where('id', $id)
            ->where(
                'deleted_at IS NULL',
                null,
                false
            )
            ->where(
                'status !=',
                $new_status
            )
            ->update(
                $this->table,
                [
                    'status' => $new_status,
                    'updated_by' => $employee_no,
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
    }

    public function update_current_enrollment(
    $student_id,
    array $data
    ) {
        $enrollment =
            $this->db
                ->where(
                    'student_id',
                    $student_id
                )
                ->where(
                    'status',
                    'active'
                )
                ->get(
                    'student_enrollments'
                )
                ->row();


        /*
        |--------------------------------------------------------------------------
        | No Active Enrollment
        |--------------------------------------------------------------------------
        */

        if (!$enrollment) {

            $data['student_id'] =
                $student_id;

            $data['status'] =
                'active';


            return $this->db->insert(
                'student_enrollments',
                $data
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update Existing Enrollment
        |--------------------------------------------------------------------------
        */

        return $this->db
            ->where(
                'id',
                $enrollment->id
            )
            ->update(
                'student_enrollments',
                $data
            );
    }


    /**
     * Create student enrollment.
     *
     * @param array $data
     * @return int|false
     */
    public function create_enrollment($data)
    {
        $this->db->insert(
            'student_enrollments',
            $data
        );

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function update_enrollment(
        $student_id,
        array $data
    ) {
        $enrollment =
            $this->db
                ->where(
                    'student_id',
                    $student_id
                )
                ->where(
                    'status',
                    'active'
                )
                ->order_by(
                    'id',
                    'DESC'
                )
                ->limit(1)
                ->get(
                    'student_enrollments'
                )
                ->row();

        if (!$enrollment) {

            $data['student_id'] =
                $student_id;

            return $this->db->insert(
                'student_enrollments',
                $data
            );
        }

        // return $this->db
        //     ->where(
        //         'id',
        //         $enrollment->id
        //     )
        //     ->update(
        //         'student_enrollments',
        //         $data
        //     );
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
                (string) ($enrollment->$field ?? '') !==
                (string) $value
            ) {

                $data['updated_by'] =
                    $this->session
                        ->userdata('employee_no');

                $data['updated_at'] =
                    date('Y-m-d H:i:s');

                return $this->db
                    ->where(
                        'id',
                        $enrollment->id
                    )
                    ->update(
                        'student_enrollments',
                        $data
                    );
            }
        }

        return true;
    }

    /**
     * Get complete student profile.
     *
     * @param int $id
     * @return object|null
     */
    public function get_student_profile($id)
    {
        $student = $this->db
            ->select('
                students.*,

                student_enrollments.id AS enrollment_id,
                student_enrollments.academic_year,
                student_enrollments.grade_level,
                student_enrollments.program,
                student_enrollments.section,
                student_enrollments.admission_type,
                student_enrollments.status AS enrollment_status,
                student_enrollments.enrolled_at
            ')
            ->from('students')
            ->join(
                'student_enrollments',
                'student_enrollments.student_id = students.id
                AND student_enrollments.status = "active"',
                'left'
            )
            ->where(
                'students.id',
                $id
            )
            ->where(
                'students.deleted_at IS NULL',
                null,
                false
            )
            ->get()
            ->row();

        return $student;
    }

    /**
     * Get complete enrollment history for a student.
     *
     * @param int $student_id
     * @return array
     */
    public function get_enrollment_history($student_id)
    {
        return $this->db
            ->select('
                id,
                student_id,
                academic_year,
                grade_level,
                section,
                admission_type,
                status,
                enrolled_at
            ')
            ->where(
                'student_id',
                $student_id
            )
            ->order_by(
                'id',
                'DESC'
            )
            ->get('student_enrollments')
            ->result();
    }


    /**
     * Get student information by LRN.
     *
     * @param string $lrn
     * @return object|null
     */
    public function get_student_by_lrn($lrn)
    {
        $this->db
            ->select([
                's.id',
                's.lrn',
                's.first_name',
                's.middle_name',
                's.last_name',
                'e.academic_year',
                'e.grade_level',
                'e.section'
            ]);

        $this->db->from('students s');

        $this->db->join(
            'student_enrollments e',
            'e.student_id = s.id',
            'left'
        );

        $this->db->where(
            's.lrn',
            $lrn
        );

        $this->db->where(
            's.deleted_at IS NULL',
            null,
            false
        );

        $this->db->order_by(
            'e.id',
            'DESC'
        );

        $this->db->limit(1);

        return $this->db
            ->get()
            ->row();
    }


    /**
     * Get unique academic years.
     */
    public function get_academic_years()
    {
        return $this->db
            ->select('year')
            ->distinct()
            ->where('status', 'Active')
            ->order_by('year', 'DESC')
            ->get('academic_sections')
            ->result();
    }


    /**
     * Get grade levels available for an academic year.
     */
    public function get_grade_levels_by_year($year)
    {
        return $this->db
            ->select('grade')
            ->distinct()
            ->where('year', $year)
            ->where('status', 'Active')
            ->order_by('grade', 'ASC')
            ->get('academic_sections')
            ->result();
    }


    /**
     * Get sections available for an academic year + grade.
     */
    public function get_sections_by_year_and_grade(
        $year,
        $grade
    ) {
        return $this->db
            ->select('id, section')
            ->where('year', $year)
            ->where('grade', $grade)
            ->where('status', 'Active')
            ->order_by('section', 'ASC')
            ->get('academic_sections')
            ->result();
    }

    /**
     * Get current active enrollment.
     *
     * @param int $student_id
     * @return object|null
     */
    public function get_current_enrollment(
        $student_id
    ) {
        return $this->db
            ->where(
                'student_id',
                $student_id
            )
            ->where(
                'status',
                'active'
            )
            ->order_by(
                'id',
                'DESC'
            )
            ->limit(1)
            ->get(
                'student_enrollments'
            )
            ->row();
    }

}