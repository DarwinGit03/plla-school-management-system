<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Academic_section_model extends CI_Model
{
    protected $table = 'academic_sections';

    public function get_sections($filters = [])
    {
        $this->db
            ->select('sections.id, sections.year, sections.grade, sections.section, sections.status, COUNT(enrollments.id) AS enrolled_count')
            ->from($this->table . ' AS sections')
            ->join(
                'student_enrollments AS enrollments',
                'enrollments.academic_year = sections.year AND (enrollments.grade_level = sections.grade OR enrollments.grade_level = CONCAT("Grade ", sections.grade) OR (sections.grade = "N" AND enrollments.grade_level IN ("N", "Nursery")) OR (sections.grade = "K" AND enrollments.grade_level IN ("K", "Kindergarten"))) AND (enrollments.section = sections.section OR CAST(enrollments.section AS CHAR) = CAST(sections.id AS CHAR))',
                'left'
            );

        if (!empty($filters['year'])) {
            $this->db->where('sections.year', $filters['year']);
        }
        if (!empty($filters['grade'])) {
            $this->db->where('sections.grade', $filters['grade']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('sections.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('sections.section', $filters['search'])
                ->or_like('sections.year', $filters['search'])
                ->or_like('sections.grade', $filters['search'])
                ->group_end();
        }

        return $this->db
            ->group_by([
                'sections.id',
                'sections.year',
                'sections.grade',
                'sections.section',
                'sections.status'
            ])
            ->order_by('sections.year', 'DESC')
            ->order_by('sections.grade', 'ASC')
            ->order_by('sections.section', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->get($this->table)
            ->row();
    }

    public function exists_duplicate($year, $grade, $section, $exclude_id = 0)
    {
        $this->db
            ->where('year', $year)
            ->where('grade', $grade)
            ->where('section', $section);

        if ((int) $exclude_id > 0) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function create_section($data)
    {
        $this->db->set([
            'year' => $data['year'],
            'grade' => $data['grade'],
            'section' => $data['section'],
            'status' => $data['status'],
            'created_by' => $data['created_by']
        ]);
        $this->db->set('created_at', 'CURRENT_TIMESTAMP', false);
        $inserted = $this->db->insert($this->table);

        return $inserted ? (int) $this->db->insert_id() : false;
    }

    public function update_section($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->set([
                'year' => $data['year'],
                'grade' => $data['grade'],
                'section' => $data['section'],
                'status' => $data['status'],
                'updated_by' => $data['updated_by']
            ])
            ->set('updated_at', 'CURRENT_TIMESTAMP', false)
            ->update($this->table);
    }

    public function update_status($id, $status, $employee_no)
    {
        return $this->db
            ->where('id', (int) $id)
            ->set(['status' => $status, 'updated_by' => $employee_no])
            ->set('updated_at', 'CURRENT_TIMESTAMP', false)
            ->update($this->table);
    }
}
