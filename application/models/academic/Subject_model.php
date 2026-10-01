<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subject_model extends CI_Model
{
    public function get_subjects(array $filters = [])
    {
        $this->db->from('academic_subjects AS subjects');
        if (isset($filters['is_active'])) $this->db->where('subjects.is_active', (int) $filters['is_active']);
        if (!empty($filters['school_year'])) $this->db->where('subjects.school_year', $filters['school_year']);
        if (!empty($filters['grade_code'])) $this->db->where('subjects.grade_code', $filters['grade_code']);
        if (!empty($filters['scoped_only'])) {
            $this->db->where('subjects.school_year IS NOT NULL', null, false)
                ->where('subjects.grade_code IS NOT NULL', null, false);
        }
        if (!empty($filters['unassigned_only'])) {
            $this->db->where('NOT EXISTS (SELECT 1 FROM employee_subject_assignments assignments WHERE assignments.subject_id = subjects.id AND assignments.status = "active")', null, false);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('subjects.subject_name', $filters['search'])
                ->or_like('subjects.subject_code', $filters['search'])
                ->group_end();
        }
        return $this->db->order_by('subjects.school_year', 'DESC')->order_by('subjects.grade_code', 'ASC')->order_by('subjects.subject_name', 'ASC')->get()->result();
    }

    public function get_subject($id)
    {
        return $this->db->where('id', (int) $id)->get('academic_subjects')->row();
    }

    public function save_subject(array $data, $id = 0)
    {
        $this->db->where('subject_name', $data['subject_name'])
            ->where('school_year', $data['school_year'])
            ->where('grade_code', $data['grade_code']);
        if ((int) $id > 0) $this->db->where('id !=', (int) $id);
        if ($this->db->count_all_results('academic_subjects') > 0) {
            return ['ok' => false, 'code' => 'duplicate_name'];
        }

        if ($data['subject_code'] !== '') {
            $this->db->where('subject_code', $data['subject_code'])
                ->where('school_year', $data['school_year'])
                ->where('grade_code', $data['grade_code']);
            if ((int) $id > 0) $this->db->where('id !=', (int) $id);
            if ($this->db->count_all_results('academic_subjects') > 0) {
                return ['ok' => false, 'code' => 'duplicate_code'];
            }
        }

        $values = [
            'school_year' => $data['school_year'],
            'grade_code' => $data['grade_code'],
            'subject_code' => $data['subject_code'] !== '' ? $data['subject_code'] : null,
            'subject_name' => $data['subject_name'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'updated_by' => $data['updated_by'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ((int) $id > 0) {
            $existing = $this->get_subject($id);
            if (!$existing) return ['ok' => false, 'code' => 'save_failed'];
            if (($existing->school_year !== $data['school_year'] || $existing->grade_code !== $data['grade_code'])
                && $this->db->where('subject_id', (int) $id)->count_all_results('employee_subject_assignments') > 0) {
                return ['ok' => false, 'code' => 'assigned_scope_locked'];
            }
            $updated = $this->db->where('id', (int) $id)->update('academic_subjects', $values);
            return $updated ? ['ok' => true, 'id' => (int) $id] : ['ok' => false, 'code' => 'save_failed'];
        }

        $values['is_active'] = 1;
        $values['created_by'] = $data['updated_by'];
        $inserted = $this->db->insert('academic_subjects', $values);
        return $inserted ? ['ok' => true, 'id' => (int) $this->db->insert_id()] : ['ok' => false, 'code' => 'save_failed'];
    }

    public function update_subject_status($id, $is_active, $updated_by)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update('academic_subjects', [
                'is_active' => (int) $is_active,
                'updated_by' => (string) $updated_by,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function get_assignments(array $filters = [])
    {
        $this->db
            ->select('assignments.id, assignments.employee_id, assignments.subject_id, assignments.status, assignments.assigned_by, assignments.created_at, assignments.updated_at, subjects.subject_code, subjects.subject_name, COALESCE(subjects.school_year, assignments.school_year) AS school_year, COALESCE(subjects.grade_code, assignments.grade_code) AS grade_code, employees.employee_no, employees.first_name, employees.middle_name, employees.last_name, employees.position_title', false)
            ->from('employee_subject_assignments AS assignments')
            ->join('academic_subjects AS subjects', 'subjects.id = assignments.subject_id')
            ->join('employees', 'employees.id = assignments.employee_id');
        if (!empty($filters['employee_id'])) $this->db->where('assignments.employee_id', (int) $filters['employee_id']);
        if (!empty($filters['subject_id'])) $this->db->where('assignments.subject_id', (int) $filters['subject_id']);
        if (!empty($filters['school_year'])) $this->db->where('COALESCE(subjects.school_year, assignments.school_year) = ' . $this->db->escape($filters['school_year']), null, false);
        if (!empty($filters['grade_code'])) $this->db->where('COALESCE(subjects.grade_code, assignments.grade_code) = ' . $this->db->escape($filters['grade_code']), null, false);
        if (isset($filters['status']) && $filters['status'] !== '') $this->db->where('assignments.status', $filters['status']);
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('subjects.subject_name', $filters['search'])
                ->or_like('subjects.subject_code', $filters['search'])
                ->or_like('employees.employee_no', $filters['search'])
                ->or_like('employees.first_name', $filters['search'])
                ->or_like('employees.middle_name', $filters['search'])
                ->or_like('employees.last_name', $filters['search'])
                ->group_end();
        }
        return $this->db
            ->order_by('school_year', 'DESC')
            ->order_by('grade_code', 'ASC')
            ->order_by('subjects.subject_name', 'ASC')
            ->order_by('employees.last_name', 'ASC')
            ->get()->result();
    }

    public function assign_teacher(array $data)
    {
        $this->db->trans_begin();
        $subject = $this->db->query('SELECT id FROM academic_subjects WHERE id = ? AND is_active = 1 FOR UPDATE', [(int) $data['subject_id']])->row();
        if (!$subject) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $current = $this->db->where('subject_id', (int) $data['subject_id'])
            ->where('status', 'active')
            ->get('employee_subject_assignments')->row();
        if ($current) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'already_assigned'];
        }

        $inserted = $this->db->insert('employee_subject_assignments', [
            'employee_id' => (int) $data['employee_id'],
            'subject_id' => (int) $data['subject_id'],
            'school_year' => $data['school_year'],
            'grade_code' => $data['grade_code'],
            'status' => 'active',
            'assigned_by' => $data['assigned_by']
        ]);
        if (!$inserted || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $id = (int) $this->db->insert_id();
        $this->db->trans_commit();
        return ['ok' => true, 'id' => $id];
    }

    public function update_assignment_status($id, $status)
    {
        $assignment = $this->db->where('id', (int) $id)->get('employee_subject_assignments')->row();
        if (!$assignment) return false;

        $this->db->trans_begin();
        $subject = $this->db->query('SELECT id FROM academic_subjects WHERE id = ? FOR UPDATE', [(int) $assignment->subject_id])->row();
        if (!$subject) {
            $this->db->trans_rollback();
            return false;
        }
        if ($status === 'active'
            && $this->db->where('subject_id', (int) $assignment->subject_id)
                ->where('status', 'active')
                ->where('id !=', (int) $id)
                ->count_all_results('employee_subject_assignments') > 0) {
            $this->db->trans_rollback();
            return false;
        }

        $updated = $this->db->where('id', (int) $id)->update('employee_subject_assignments', [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        if (!$updated || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function get_assignment($id)
    {
        return $this->db
            ->select('assignments.id, assignments.employee_id, assignments.subject_id, assignments.status, subjects.school_year AS subject_school_year, subjects.grade_code AS subject_grade_code, subjects.subject_name, subjects.subject_code')
            ->from('employee_subject_assignments AS assignments')
            ->join('academic_subjects AS subjects', 'subjects.id = assignments.subject_id')
            ->where('assignments.id', (int) $id)
            ->get()->row();
    }

    public function get_assignment_for_employee($assignmentId, $employeeId)
    {
        return $this->db
            ->select('assignments.id, assignments.employee_id, assignments.subject_id, assignments.status, COALESCE(subjects.school_year, assignments.school_year) AS offering_school_year, COALESCE(subjects.grade_code, assignments.grade_code) AS offering_grade_code, subjects.subject_name, subjects.subject_code', false)
            ->from('employee_subject_assignments AS assignments')
            ->join('academic_subjects AS subjects', 'subjects.id = assignments.subject_id')
            ->where('assignments.id', (int) $assignmentId)
            ->where('assignments.employee_id', (int) $employeeId)
            ->where('assignments.status', 'active')
            ->where('subjects.is_active', 1)
            ->get()->row();
    }
}
