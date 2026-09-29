<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Persists enrollment placement and its fee assessment.
 *
 * Enrollment_service prepares and validates the assessment; this model owns
 * the related database transaction and table writes.
 */
class Enrollment_model extends CI_Model
{
    protected $enrollments_table = 'student_enrollments';
    protected $fees_table = 'student_enrollment_fees';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('finance/fee_configuration_model');
    }

    public function has_enrollment_payments($enrollment_id)
    {
        return $this->db
            ->from('student_fee_payments AS payments')
            ->join($this->fees_table . ' AS assessed_fees', 'assessed_fees.id = payments.enrollment_fee_id', 'inner')
            ->where('assessed_fees.enrollment_id', (int) $enrollment_id)
            ->count_all_results() > 0;
    }

    public function get_enrollment_uniform_size($enrollment_id)
    {
        $row = $this->db->select('uniforms.uniform_size')
            ->from($this->fees_table . ' AS assessed_fees')
            ->join('fee_uniform AS uniforms', 'uniforms.id = assessed_fees.source_fee_id', 'inner')
            ->where('assessed_fees.enrollment_id', (int) $enrollment_id)
            ->where('assessed_fees.fee_category', 'uniform')
            ->limit(1)->get()->row();
        return $row ? (string) $row->uniform_size : '';
    }

    /** Update the active enrollment and replace its fee snapshot before payments exist. */
    public function update_enrollment_assessment($student_id, $enrollment_id, array $placement, array $fee_lines, $employee_no)
    {
        $this->db->trans_begin();
        $locked = $this->db->query('SELECT id FROM students WHERE id = ? FOR UPDATE', [(int) $student_id]);
        if (!$locked || $this->db->trans_status() === false) return $this->rollback_with_error('save_failed');

        $enrollment = $this->db->where('id', (int) $enrollment_id)
            ->where('student_id', (int) $student_id)
            ->where('status', 'active')
            ->get($this->enrollments_table)->row();
        if (!$enrollment) return $this->rollback_with_error('enrollment_not_found');

        $duplicate = $this->db->where('student_id', (int) $student_id)
            ->where('academic_year', $placement['academic_year'])
            ->where('id !=', (int) $enrollment_id)
            ->limit(1)->get($this->enrollments_table)->row();
        if ($duplicate) return $this->rollback_with_error('duplicate_enrollment');

        if ($this->has_enrollment_payments($enrollment_id)) {
            return $this->rollback_with_error('has_payments');
        }

        $updated = $this->db->where('id', (int) $enrollment_id)->update($this->enrollments_table, [
            'academic_year' => $placement['academic_year'],
            'grade_level' => $this->fee_configuration_model->grade_label($placement['grade_level']),
            'payment_mode' => $placement['payment_mode'],
            'fee_configuration_id' => $placement['fee_configuration_id'],
            'section' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $employee_no
        ]);
        if (!$updated) return $this->rollback_with_error('save_failed');

        $this->db->where('enrollment_id', (int) $enrollment_id)->delete($this->fees_table);
        foreach ($fee_lines as $line) {
            $line['enrollment_id'] = (int) $enrollment_id;
            if (!$this->db->insert($this->fees_table, $line)) return $this->rollback_with_error('assessment_save_failed');
        }
        if ($this->db->trans_status() === false) return $this->rollback_with_error('save_failed');
        $this->db->trans_commit();
        return ['ok' => true, 'code' => 'updated', 'enrollment_id' => (int) $enrollment_id];
    }

    /**
     * Save an enrollment and its immutable fee snapshot atomically.
     *
     * @return array{ok:bool, code:string, enrollment_id?:int}
     */
    public function save_enrollment_assessment(
        $student_id,
        $lrn,
        array $placement,
        array $fee_lines,
        $employee_no
    ) {
        $this->db->trans_begin();

        // Serialize enrollment changes for a student to prevent double submits.
        $locked = $this->db->query(
            'SELECT id FROM students WHERE id = ? FOR UPDATE',
            [$student_id]
        );
        if (!$locked || $this->db->trans_status() === false) {
            return $this->rollback_with_error('save_failed');
        }

        $current = $this->db
            ->where('student_id', $student_id)
            ->where('status', 'active')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->enrollments_table)
            ->row();

        $enrollment_id = null;
        if ($current) {
            $current_year_start = $this->school_year_start($current->academic_year);
            $target_year_start = $this->school_year_start($placement['academic_year']);
            if ($current_year_start !== null && $target_year_start !== null) {
                if ($target_year_start < $current_year_start) {
                    return $this->rollback_with_error('year_regression');
                }
                if ($target_year_start === $current_year_start) {
                    if ((string) $current->academic_year !== (string) $placement['academic_year']) {
                        return $this->rollback_with_error('year_regression');
                    }
                    if (empty($placement['allow_current_year_setup'])) {
                        return $this->rollback_with_error('same_year_not_allowed');
                    }
                    if ($this->fee_configuration_model->grade_code($current->grade_level) !== $this->fee_configuration_model->grade_code($placement['grade_level'])) {
                        return $this->rollback_with_error('same_year_grade_change');
                    }
                } elseif ($this->grade_rank($placement['grade_level']) < $this->grade_rank($current->grade_level)) {
                    return $this->rollback_with_error('grade_regression');
                }
            }
        }

        if ($current && (string) $current->academic_year === (string) $placement['academic_year']) {
            if (!empty($current->fee_configuration_id)) {
                return $this->rollback_with_error('already_assessed');
            }

            $existing_fees = $this->db
                ->where('enrollment_id', $current->id)
                ->count_all_results($this->fees_table);
            if ($existing_fees > 0) {
                return $this->rollback_with_error('fees_already_exist');
            }

            $enrollment_id = (int) $current->id;
            $this->db
                ->where('id', $enrollment_id)
                ->update($this->enrollments_table, [
                    'grade_level' => $this->fee_configuration_model->grade_label($placement['grade_level']),
                    'payment_mode' => $placement['payment_mode'],
                    'fee_configuration_id' => $placement['fee_configuration_id'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $employee_no
                ]);
        } else {
            $duplicate = $this->db
                ->where('student_id', $student_id)
                ->where('academic_year', $placement['academic_year'])
                ->limit(1)
                ->get($this->enrollments_table)
                ->row();
            if ($duplicate) {
                return $this->rollback_with_error('duplicate_enrollment');
            }

            if ($current) {
                $this->db
                    ->where('student_id', $student_id)
                    ->where('status', 'active')
                    ->update($this->enrollments_table, [
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => $employee_no
                    ]);
            }

            $history_count = $this->db
                ->where('student_id', $student_id)
                ->count_all_results($this->enrollments_table);

            $inserted = $this->db->insert($this->enrollments_table, [
                'student_id' => $student_id,
                'lrn' => $lrn,
                'academic_year' => $placement['academic_year'],
                'grade_level' => $this->fee_configuration_model->grade_label($placement['grade_level']),
                'section' => null,
                'payment_mode' => $placement['payment_mode'],
                'fee_configuration_id' => $placement['fee_configuration_id'],
                'admission_type' => $history_count === 0 ? 'New Student' : 'Returning Student',
                'status' => 'active',
                'enrolled_at' => date('Y-m-d H:i:s'),
                'created_by' => $employee_no
            ]);
            if (!$inserted || $this->db->affected_rows() < 1) {
                return $this->rollback_with_error('create_failed');
            }

            $enrollment_id = (int) $this->db->insert_id();
        }

        foreach ($fee_lines as $line) {
            $line['enrollment_id'] = $enrollment_id;
            if (!$this->db->insert($this->fees_table, $line)) {
                return $this->rollback_with_error('assessment_save_failed');
            }
        }

        if ($this->db->trans_status() === false) {
            return $this->rollback_with_error('save_failed');
        }

        $this->db->trans_commit();

        return [
            'ok' => true,
            'code' => 'saved',
            'enrollment_id' => $enrollment_id
        ];
    }

    /** Roll back a failed operation and return its stable result code. */
    private function rollback_with_error($code)
    {
        $this->db->trans_rollback();

        return [
            'ok' => false,
            'code' => $code
        ];
    }

    private function school_year_start($school_year)
    {
        return preg_match('/^(\d{4})-(\d{4})$/', (string) $school_year, $matches)
            ? (int) $matches[1]
            : null;
    }

    private function grade_rank($grade)
    {
        $grade = strtoupper(trim((string) $grade));
        if (preg_match('/^GRADE\s*(\d+)$/', $grade, $matches)) $grade = $matches[1];
        if ($grade === 'N' || $grade === 'NURSERY') return 0;
        if ($grade === 'K' || $grade === 'KINDERGARTEN') return 1;
        if (ctype_digit($grade)) return (int) $grade + 1;
        return 0;
    }
}
