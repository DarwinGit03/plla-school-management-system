<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Teacher_model extends CI_Model
{
    protected $table = 'teachers';

    public function get_teachers(array $filters = [])
    {
        $latestRateSql = '(SELECT rates.teacher_id, rates.salary_type, rates.pay_frequency, rates.monthly_salary, rates.daily_salary
            FROM teacher_salary_history AS rates
            INNER JOIN (
                SELECT teacher_id, MAX(effective_from) AS effective_from
                FROM teacher_salary_history
                WHERE effective_from <= CURRENT_DATE
                GROUP BY teacher_id
            ) AS current_rate ON current_rate.teacher_id = rates.teacher_id
                AND current_rate.effective_from = rates.effective_from) AS current_salary';
        $this->db
            ->select('teachers.*, current_salary.salary_type AS salary_type, current_salary.pay_frequency AS pay_frequency, current_salary.monthly_salary AS monthly_salary, current_salary.daily_salary AS daily_salary')
            ->from($this->table . ' AS teachers')
            ->join($latestRateSql, 'current_salary.teacher_id = teachers.id', 'left', false);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('teachers.status', $filters['status']);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $this->db->group_start()
                ->like('teachers.employee_no', $search)
                ->or_like('teachers.first_name', $search)
                ->or_like('teachers.middle_name', $search)
                ->or_like('teachers.last_name', $search)
                ->or_like('teachers.work_email', $search)
                ->or_like('teachers.position_title', $search)
                ->group_end();
        }

        return $this->db
            ->order_by('teachers.last_name', 'ASC')
            ->order_by('teachers.first_name', 'ASC')
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

    public function get_by_user_id($user_id)
    {
        return $this->db
            ->where('user_id', (int) $user_id)
            ->get($this->table)
            ->row();
    }

    public function get_linkable_user_accounts($teacher_id = 0)
    {
        return $this->db
            ->select('users.id, users.employee_no, users.first_name, users.last_name')
            ->from('users')
            ->join('teachers AS linked_teacher', 'linked_teacher.user_id = users.id', 'left')
            ->where('users.role_id', 3)
            ->group_start()
                ->where('linked_teacher.id IS NULL', null, false)
                ->or_where('linked_teacher.id', (int) $teacher_id)
            ->group_end()
            ->order_by('users.last_name', 'ASC')
            ->order_by('users.first_name', 'ASC')
            ->get()
            ->result();
    }

    private function can_link_user_account($user_id, $teacher_id = 0)
    {
        if (empty($user_id)) {
            return true;
        }
        $account = $this->db
            ->select('users.id')
            ->from('users')
            ->join('teachers AS linked_teacher', 'linked_teacher.user_id = users.id', 'left')
            ->where('users.id', (int) $user_id)
            ->where('users.role_id', 3)
            ->group_start()
                ->where('linked_teacher.id IS NULL', null, false)
                ->or_where('linked_teacher.id', (int) $teacher_id)
            ->group_end()
            ->get()
            ->row();
        return (bool) $account;
    }

    public function employee_no_exists($employee_no, $exclude_id = 0)
    {
        $this->db->where('employee_no', trim((string) $employee_no));
        if ((int) $exclude_id > 0) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function save(array $data, $id = 0)
    {
        if ((int) $id > 0) {
            if (array_key_exists('user_id', $data) && !$this->can_link_user_account($data['user_id'], $id)) {
                return false;
            }
            $this->db->trans_begin();
            $saved = $this->db
                ->where('id', (int) $id)
                ->set($data)
                ->set('updated_at', 'CURRENT_TIMESTAMP', false)
                ->update($this->table);
            if (!$saved || $this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
            $this->db->trans_commit();
            return true;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data)
            ? (int) $this->db->insert_id()
            : false;
    }

    public function register_with_salary(array $profile, $salary_type, $pay_frequency, $amount, $effective_from, $change_reason)
    {
        if (array_key_exists('user_id', $profile) && !$this->can_link_user_account($profile['user_id'])) {
            return ['ok' => false, 'code' => 'invalid_user_link'];
        }
        $this->db->trans_begin();
        $profile['created_at'] = date('Y-m-d H:i:s');
        if (!$this->db->insert($this->table, $profile)) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }

        $teacherId = (int) $this->db->insert_id();
        $salaryId = $teacherId > 0
            ? $this->insert_salary_rate($teacherId, $salary_type, $pay_frequency, $amount, $effective_from, $profile['created_by'] ?? null, $change_reason)
            : false;
        if ($teacherId < 1 || !$salaryId) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'teacher_id' => $teacherId, 'salary_id' => $salaryId];
    }

    public function update_status($id, $status, $updated_by)
    {
        return $this->db
            ->where('id', (int) $id)
            ->set([
                'status' => $status,
                'updated_by' => (string) $updated_by
            ])
            ->set('updated_at', 'CURRENT_TIMESTAMP', false)
            ->update($this->table);
    }

    public function get_salary_history($teacher_id)
    {
        return $this->db
            ->where('teacher_id', (int) $teacher_id)
            ->order_by('effective_from', 'DESC')
            ->order_by('id', 'DESC')
            ->get('teacher_salary_history')
            ->result();
    }

    public function get_salary_for_date($teacher_id, $date)
    {
        return $this->db
            ->where('teacher_id', (int) $teacher_id)
            ->where('effective_from <=', $date)
            ->group_start()
                ->where('effective_to IS NULL', null, false)
                ->or_where('effective_to >=', $date)
            ->group_end()
            ->order_by('effective_from', 'DESC')
            ->limit(1)
            ->get('teacher_salary_history')
            ->row();
    }

    public function add_salary_rate($teacher_id, $salary_type, $pay_frequency, $amount, $effective_from, $created_by, $change_reason)
    {
        $this->db->trans_begin();
        $this->db
            ->select('id, effective_from, effective_to')
            ->where('teacher_id', (int) $teacher_id)
            ->order_by('effective_from', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1);
        $latestQuery = $this->db->get_compiled_select('teacher_salary_history');
        $latest = $this->db->query($latestQuery . ' FOR UPDATE')->row();

        if ($latest && $effective_from <= $latest->effective_from) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'date_order'];
        }

        if ($latest && empty($latest->effective_to)) {
            $previousEnd = (new DateTime($effective_from))->modify('-1 day')->format('Y-m-d');
            $this->db->where('id', (int) $latest->id)->update('teacher_salary_history', [
                'effective_to' => $previousEnd
            ]);
        }

        $salaryId = $this->insert_salary_rate($teacher_id, $salary_type, $pay_frequency, $amount, $effective_from, $created_by, $change_reason);
        if (!$salaryId || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'code' => 'save_failed'];
        }

        $this->db->trans_commit();
        return ['ok' => true, 'id' => $salaryId];
    }

    private function insert_salary_rate($teacher_id, $salary_type, $pay_frequency, $amount, $effective_from, $created_by, $change_reason)
    {
        if (!$this->db->insert('teacher_salary_history', [
            'teacher_id' => (int) $teacher_id,
            'salary_type' => $salary_type,
            'pay_frequency' => $pay_frequency,
            'monthly_salary' => $salary_type === 'monthly' ? number_format((float) $amount, 2, '.', '') : null,
            'daily_salary' => $salary_type === 'daily' ? number_format((float) $amount, 2, '.', '') : null,
            'effective_from' => $effective_from,
            'change_reason' => trim((string) $change_reason),
            'created_by' => (string) $created_by,
            'created_at' => date('Y-m-d H:i:s')
        ])) {
            return false;
        }
        $salaryId = (int) $this->db->insert_id();
        return $salaryId > 0 ? $salaryId : false;
    }
}
