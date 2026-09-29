<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Fee_configuration_model extends CI_Model
{
    protected $table = 'fee_configurations';

    public function __construct()
    {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Configuration
    |--------------------------------------------------------------------------
    | Finds the fee configuration using:
    |
    | School Year
    | Grade Code
    | Payment Mode
    |
    */

    public function get_configuration(
        $school_year,
        $grade_code,
        $payment_mode
    )
    {
        return $this->db
            ->where('school_year', $school_year)
            ->where('grade_code', $grade_code)
            ->where('payment_mode', $payment_mode)
            ->where('status', 'active')
            ->get($this->table)
            ->row();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Tuition
    |--------------------------------------------------------------------------
    */

    public function get_tuition($configuration_id)
    {
        return $this->db
            ->where('configuration_id', $configuration_id)
            ->where('status', 'active')
            ->order_by('payment_sequence', 'ASC')
            ->get('fee_tuition')
            ->result();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Worktext
    |--------------------------------------------------------------------------
    |
    | Worktext is based on:
    |
    | School Year
    | Grade Code
    |
    | It does NOT depend on payment mode.
    |
    */

    public function get_worktext(
        $school_year,
        $grade_code
    )
    {
        return $this->db
            ->where('school_year', $school_year)
            ->where('grade_code', $grade_code)
            ->where('status', 'active')
            ->order_by('payment_sequence', 'ASC')
            ->get('fee_worktext')
            ->result();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Extra Curricular
    |--------------------------------------------------------------------------
    |
    | Also based only on:
    |
    | School Year
    | Grade Code
    |
    */

    public function get_extra_curricular(
        $school_year,
        $grade_code
    )
    {
        return $this->db
            ->where('school_year', $school_year)
            ->where('grade_code', $grade_code)
            ->where('status', 'active')
            ->order_by('payment_sequence', 'ASC')
            ->get('fee_extra_curricular')
            ->result();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Uniforms
    |--------------------------------------------------------------------------
    |
    | Uniform is based on:
    |
    | School Year
    | Uniform Type
    |
    */

    public function get_uniforms(
        $school_year,
        $uniform_type = null
    )
    {
        $this->db
            ->where('school_year', $school_year)
            ->where('status', 'active');

        if ($uniform_type !== null) {
            $this->db->where('uniform_type', $uniform_type);
        }

        return $this->db
            ->order_by('id', 'ASC')
            ->get('fee_uniform')
            ->result();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Complete Fee Configuration
    |--------------------------------------------------------------------------
    |
    | This is the main method that Enrollment will eventually use.
    |
    */

    public function get_complete_fees(
        $school_year,
        $grade_code,
        $payment_mode
    )
    {
        $configuration = $this->get_configuration(
            $school_year,
            $grade_code,
            $payment_mode
        );

        if (!$configuration) {
            return null;
        }

        return array(
            'configuration' => $configuration,

            'tuition' => $this->get_tuition(
                $configuration->id
            ),

            'worktext' => $this->get_worktext(
                $school_year,
                $grade_code
            ),

            'extra_curricular' => $this->get_extra_curricular(
                $school_year,
                $grade_code
            ),

            'uniform_boys' => $this->get_uniforms(
                $school_year,
                'boys'
            ),

            'uniform_girls' => $this->get_uniforms(
                $school_year,
                'girls'
            )
        );
    }
    
    public function get_configurations()
    {
        return $this->db
            ->order_by('school_year', 'DESC')
            ->order_by('grade_code', 'ASC')
            ->order_by('payment_mode', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_editable_fees($configuration)
    {
        $tuition = $this->db->where('configuration_id', $configuration->id)->order_by('payment_sequence', 'ASC')->get('fee_tuition')->result();
        $worktext = $this->db->where(['school_year' => $configuration->school_year, 'grade_code' => $configuration->grade_code])->order_by('payment_sequence', 'ASC')->get('fee_worktext')->result();
        $extra = $this->db->where(['school_year' => $configuration->school_year, 'grade_code' => $configuration->grade_code])->order_by('payment_sequence', 'ASC')->get('fee_extra_curricular')->result();
        $uniforms = $this->db->where('school_year', $configuration->school_year)->order_by('uniform_type', 'ASC')->order_by('uniform_size', 'ASC')->get('fee_uniform')->result();
        return ['tuition' => $tuition, 'worktext' => $worktext, 'extra_curricular' => $extra, 'uniforms' => $uniforms];
    }

    public function get_school_years()
    {
        return $this->db->distinct()->select('school_year')->order_by('school_year', 'DESC')
            ->get($this->table)->result();
    }

    /** Inventory school years which already contain any fee schedule records. */
    public function get_fee_year_inventory()
    {
        $inventory = [];
        $sources = [
            $this->table => 'configurations',
            'fee_worktext' => 'worktext',
            'fee_extra_curricular' => 'extra_curricular',
            'fee_uniform' => 'uniforms'
        ];
        foreach ($sources as $table => $key) {
            $rows = $this->db->select('school_year, COUNT(*) AS row_count')->group_by('school_year')->get($table)->result();
            foreach ($rows as $row) {
                $year = (string) $row->school_year;
                if (!isset($inventory[$year])) {
                    $inventory[$year] = ['configurations' => 0, 'worktext' => 0, 'extra_curricular' => 0, 'uniforms' => 0];
                }
                $inventory[$year][$key] = (int) $row->row_count;
            }
        }
        krsort($inventory, SORT_STRING);
        return $inventory;
    }

    public function get_configurations_by_year($school_year = '')
    {
        if ($school_year !== '') {
            $this->db->where('school_year', $school_year);
        }
        return $this->db->order_by('school_year', 'DESC')->order_by('grade_code', 'ASC')
            ->order_by('payment_mode', 'ASC')->get($this->table)->result();
    }

    public function update_configuration_schedule($configuration_id, array $data)
    {
        $configuration = $this->get_configuration_by_id($configuration_id);
        if (!$configuration) return false;
        $status = $data['status'] ?? '';
        if (!in_array($status, ['draft', 'active', 'inactive'], true)) return false;
        $this->db->trans_begin();
        $this->db->where('id', $configuration_id)->update($this->table, ['status' => $status]);

        $groups = [
            'tuition' => ['table' => 'fee_tuition', 'scope' => ['configuration_id' => $configuration_id], 'amount' => 'tuition_fee'],
            'worktext' => ['table' => 'fee_worktext', 'scope' => ['school_year' => $configuration->school_year, 'grade_code' => $configuration->grade_code], 'amount' => 'amount'],
            'extra_curricular' => ['table' => 'fee_extra_curricular', 'scope' => ['school_year' => $configuration->school_year, 'grade_code' => $configuration->grade_code], 'amount' => 'amount']
        ];
        foreach ($groups as $key => $group) {
            foreach (($data['fees'][$key] ?? []) as $id => $fee) {
                if (!isset($fee['payment_label'], $fee['amount'])) { $this->db->trans_rollback(); return false; }
                $amount = filter_var($fee['amount'], FILTER_VALIDATE_FLOAT);
                if ($amount === false || $amount < 0) { $this->db->trans_rollback(); return false; }
                $update = [
                    'payment_label' => trim($fee['payment_label']),
                    'payment_date' => trim($fee['payment_date'] ?? '') ?: null,
                    $group['amount'] => $amount,
                    'status' => !empty($fee['active']) ? 'active' : 'inactive'
                ];
                $this->db->where('id', (int) $id)->where($group['scope'])->update($group['table'], $update);
            }
        }
        foreach (($data['uniforms'] ?? []) as $id => $uniform) {
            $top = filter_var($uniform['top_price'] ?? null, FILTER_VALIDATE_FLOAT);
            $bottom = filter_var($uniform['bottom_price'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($top === false || $bottom === false || $top < 0 || $bottom < 0) { $this->db->trans_rollback(); return false; }
            $this->db->where('id', (int) $id)->where('school_year', $configuration->school_year)->update('fee_uniform', [
                'top_price' => $top,
                'bottom_price' => $bottom,
                'status' => !empty($uniform['active']) ? 'active' : 'inactive'
            ]);
        }
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return false; }
        $this->db->trans_commit();
        return true;
    }

    /** Copy every configuration and fee schedule for one complete academic year. */
    public function duplicate_school_year($source_year, $target_year, $configuration_status = 'draft')
    {
        if ($source_year === '' || $target_year === '' || $source_year === $target_year || !in_array($configuration_status, ['draft', 'active'], true)) {
            return ['ok' => false, 'message' => 'Choose different source and target school years.'];
        }

        $source_configurations = $this->db->where('school_year', $source_year)->order_by('grade_code', 'ASC')->order_by('payment_mode', 'ASC')->get($this->table)->result();
        if (!$source_configurations) return ['ok' => false, 'message' => 'The selected source year has no fee configurations to copy.'];

        $inventory = $this->get_fee_year_inventory();
        $existing = $inventory[$target_year] ?? null;
        if ($existing) {
            $labels = ['configurations' => 'configurations', 'worktext' => 'worktext items', 'extra_curricular' => 'extra-curricular items', 'uniforms' => 'uniform prices'];
            $found = [];
            foreach ($labels as $key => $label) {
                if (!empty($existing[$key])) $found[] = $existing[$key] . ' ' . $label;
            }
            return ['ok' => false, 'message' => 'School year ' . $target_year . ' already contains ' . implode(', ', $found) . '. Choose an empty destination year.'];
        }

        $this->db->trans_begin();
        foreach ($source_configurations as $source) {
            $this->db->insert($this->table, [
                'school_year' => $target_year,
                'grade_code' => $source->grade_code,
                'payment_mode' => $source->payment_mode,
                'status' => $configuration_status
            ]);
            $new_id = (int) $this->db->insert_id();
            if (!$new_id || !$this->copy_rows('fee_tuition', ['configuration_id' => $source->id], ['configuration_id' => $new_id])) {
                $this->db->trans_rollback();
                return ['ok' => false, 'message' => 'The year copy failed while copying tuition schedules. No target data was kept.'];
            }
        }

        foreach (['fee_worktext', 'fee_extra_curricular'] as $table) {
            if (!$this->copy_year_rows($table, $source_year, $target_year)) {
                $this->db->trans_rollback();
                return ['ok' => false, 'message' => 'The year copy failed while copying shared fee schedules. No target data was kept.'];
            }
        }
        if (!$this->copy_year_rows('fee_uniform', $source_year, $target_year)) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The year copy failed while copying uniform prices. No target data was kept.'];
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The year copy failed. No target data was kept.'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'message' => 'All fee configurations and schedules were copied to ' . $target_year . ' as ' . $configuration_status . '.'];
    }

    /** Delete a whole unused year only when every configuration is a draft. */
    public function check_draft_school_year_deletable($school_year)
    {
        $configurations = $this->db->select('id, status')->where('school_year', $school_year)->get($this->table)->result();
        if (!$configurations) return ['ok' => false, 'message' => 'No fee configurations were found for that school year.'];
        foreach ($configurations as $configuration) {
            if ($configuration->status !== 'draft') {
                return ['ok' => false, 'message' => 'This year contains a non-draft fee configuration. Only unused draft years can be deleted.'];
            }
        }

        $configuration_ids = array_map(function ($configuration) { return (int) $configuration->id; }, $configurations);
        $used_enrollments = $this->db->where('academic_year', $school_year)->count_all_results('student_enrollments');
        if (!$used_enrollments) {
            $used_enrollments = $this->db->where_in('fee_configuration_id', $configuration_ids)->count_all_results('student_enrollments');
        }
        if ($used_enrollments) {
            return ['ok' => false, 'message' => 'This school year is referenced by student enrollments and cannot be deleted.'];
        }

        return ['ok' => true, 'message' => 'This unused draft year can be deleted.'];
    }

    public function update_school_year_status($school_year, $status)
    {
        if (!in_array($status, ['draft', 'active', 'inactive'], true)) {
            return ['ok' => false, 'message' => 'Choose a valid configuration status.'];
        }
        $count = $this->db->where('school_year', $school_year)->count_all_results($this->table);
        if (!$count) return ['ok' => false, 'message' => 'No fee configurations were found for that school year.'];

        $this->db->trans_begin();
        $this->db->where('school_year', $school_year)->update($this->table, ['status' => $status]);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The school year status could not be updated.'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'message' => 'All ' . $count . ' configurations for ' . $school_year . ' are now ' . $status . '.'];
    }

    public function delete_draft_school_year($school_year)
    {
        $eligibility = $this->check_draft_school_year_deletable($school_year);
        if (!$eligibility['ok']) return $eligibility;

        $this->db->trans_begin();
        foreach (['fee_worktext', 'fee_extra_curricular', 'fee_uniform'] as $table) {
            $this->db->where('school_year', $school_year)->delete($table);
        }
        $this->db->where('school_year', $school_year)->delete($this->table);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The school year could not be deleted.'];
        }
        $this->db->trans_commit();
        return ['ok' => true, 'message' => 'The unused draft fee year ' . $school_year . ' and its schedules were deleted.'];
    }

    private function copy_year_rows($table, $source_year, $target_year)
    {
        $rows = $this->db->where('school_year', $source_year)->get($table)->result_array();
        foreach ($rows as &$row) {
            unset($row['id'], $row['created_at'], $row['updated_at']);
            $row['school_year'] = $target_year;
        }
        unset($row);
        return empty($rows) || $this->db->insert_batch($table, $rows) !== false;
    }

    private function copy_rows($table, $where, $replace)
    {
        $rows = $this->db->where($where)->get($table)->result_array();
        foreach ($rows as &$row) {
            unset($row['id'], $row['created_at'], $row['updated_at']);
            $row = array_merge($row, $replace);
        }
        unset($row);
        return empty($rows) || $this->db->insert_batch($table, $rows) !== false;
    }

    /** Find one fee configuration by its primary key. */
    public function get_configuration_by_id($configuration_id)
    {
        return $this->db
            ->where('id', (int) $configuration_id)
            ->get($this->table)
            ->row();
    }

    /** Return school years that have at least one active fee configuration. */
    public function get_active_school_years()
    {
        return $this->db
            ->distinct()
            ->select('school_year')
            ->where('status', 'active')
            ->order_by('school_year', 'DESC')
            ->get($this->table)
            ->result();
    }

    /** Return grade codes configured for an active fee year. */
    public function get_active_grade_codes($school_year)
    {
        return $this->db
            ->distinct()
            ->select('grade_code')
            ->where('school_year', $school_year)
            ->where('status', 'active')
            ->order_by('grade_code', 'ASC')
            ->get($this->table)
            ->result();
    }
}
