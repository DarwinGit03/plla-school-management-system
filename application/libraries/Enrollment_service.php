<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Enrollment_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('student/Student_model');
        $this->CI->load->model('finance/fee_configuration_model');
        $this->CI->load->model('finance/enrollment_model');
        $this->CI->load->library('session');
    }

    /**
     * Create a new enrollment or finish fee setup for an initial enrollment.
     * Fee rows are copied into the enrollment so later configuration edits do
     * not change the student's assessed schedule.
     */
    public function save_enrollment($student_id, array $input)
    {
        $student = $this->CI->Student_model->get_student_profile($student_id);
        if (!$student) {
            return ['ok' => false, 'message' => 'Student record was not found.'];
        }

        $year = trim((string) ($input['academic_year'] ?? ''));
        $grade = trim((string) ($input['grade_code'] ?? ''));
        $payment_mode = trim((string) ($input['payment_mode'] ?? ''));
        $placement_check = $this->validate_placement(
            $student,
            $year,
            $grade,
            ($input['flow'] ?? '') === 'initial'
        );
        if (!$placement_check['ok']) return $placement_check;

        $configuration = $this->CI->fee_configuration_model->get_configuration(
            $year,
            $grade,
            $payment_mode
        );
        if (!$configuration) {
            return ['ok' => false, 'message' => 'The selected fee configuration is no longer active. Please review the enrollment details.'];
        }

        $fees = $this->CI->fee_configuration_model->get_complete_fees(
            $year,
            $grade,
            $payment_mode
        );
        if (!$fees) {
            return ['ok' => false, 'message' => 'Unable to load the selected fee configuration.'];
        }

        $uniform_type = strtolower((string) $student->gender) === 'female' ? 'girls' : 'boys';
        $uniform = null;
        $uniform_size = trim((string) ($input['uniform_size'] ?? ''));
        if ($uniform_size !== '') {
            foreach ($fees['uniform_' . $uniform_type] as $item) {
                if ((string) $item->uniform_size === $uniform_size) {
                    $uniform = $item;
                    break;
                }
            }
            if (!$uniform) {
                return ['ok' => false, 'message' => 'The selected uniform size is not available.'];
            }
        }

        $lines = [];
        foreach ($fees['tuition'] as $item) {
            $lines[] = $this->fee_line('tuition', $item->id, $item->payment_sequence, $item->payment_label, $item->payment_date, $item->tuition_fee);
        }
        foreach ($fees['worktext'] as $item) {
            $lines[] = $this->fee_line('worktext', $item->id, $item->payment_sequence, $item->payment_label, $item->payment_date, $item->amount);
        }
        foreach ($fees['extra_curricular'] as $item) {
            $lines[] = $this->fee_line('extra_curricular', $item->id, $item->payment_sequence, $item->payment_label, $item->payment_date, $item->amount);
        }
        if ($uniform) {
            $top_label = $uniform_type === 'girls' ? 'Blouse' : 'Polo';
            $bottom_label = $uniform_type === 'girls' ? 'Skirt' : 'Pants';
            $lines[] = $this->fee_line(
                'uniform',
                $uniform->id,
                null,
                ucfirst($uniform_type) . ' uniform ' . $top_label . ' · ' . $uniform->uniform_size,
                null,
                $uniform->top_price
            );
            $lines[] = $this->fee_line(
                'uniform',
                $uniform->id,
                null,
                ucfirst($uniform_type) . ' uniform ' . $bottom_label . ' · ' . $uniform->uniform_size,
                null,
                $uniform->bottom_price
            );
        }

        $result = $this->CI->enrollment_model->save_enrollment_assessment(
            (int) $student_id,
            $student->lrn,
            [
                'academic_year' => $year,
                'grade_level' => $grade,
                'payment_mode' => $payment_mode,
                'fee_configuration_id' => (int) $configuration->id,
                'allow_current_year_setup' => ($input['flow'] ?? '') === 'initial'
            ],
            $lines,
            $this->CI->session->userdata('employee_no')
        );

        if (!$result['ok']) {
            $messages = [
                'already_assessed' => 'This student already has a fee assessment for that school year.',
                'fees_already_exist' => 'This enrollment already has fee records.',
                'duplicate_enrollment' => 'An enrollment record already exists for this student and school year.',
                'year_regression' => 'The target school year must be later than the student\'s current enrollment year.',
                'same_year_not_allowed' => 'The student already has an enrollment for this school year.',
                'same_year_grade_change' => 'A student\'s grade cannot be changed within the same school year.',
                'grade_regression' => 'The target grade cannot be lower than the student\'s current grade. The same grade is allowed for a repeat year.',
                'create_failed' => 'Unable to create the enrollment record.',
                'assessment_save_failed' => 'Unable to save the fee assessment.',
                'save_failed' => 'Unable to complete enrollment. No changes were saved.'
            ];

            return [
                'ok' => false,
                'message' => $messages[$result['code']] ?? $messages['save_failed']
            ];
        }

        return [
            'ok' => true,
            'enrollment_id' => $result['enrollment_id'],
            'message' => 'Enrollment and fee assessment saved.'
        ];
    }

    /** Admin edit: refresh a saved assessment only while no payments are attached. */
    public function update_enrollment($student_id, $enrollment_id, array $input)
    {
        $student = $this->CI->Student_model->get_student_profile($student_id);
        if (!$student || (int) ($student->enrollment_id ?? 0) !== (int) $enrollment_id) {
            return ['ok' => false, 'message' => 'The active enrollment record was not found.'];
        }

        $year = trim((string) ($input['academic_year'] ?? ''));
        $grade = trim((string) ($input['grade_code'] ?? ''));
        $payment_mode = trim((string) ($input['payment_mode'] ?? ''));
        $configuration = $this->CI->fee_configuration_model->get_configuration($year, $grade, $payment_mode);
        if (!$configuration) return ['ok' => false, 'message' => 'Choose an active fee configuration before updating this enrollment.'];

        $fees = $this->CI->fee_configuration_model->get_complete_fees($year, $grade, $payment_mode);
        if (!$fees) return ['ok' => false, 'message' => 'Unable to load the selected fee configuration.'];

        $uniform_type = strtolower((string) $student->gender) === 'female' ? 'girls' : 'boys';
        $uniform = null;
        $uniform_size = trim((string) ($input['uniform_size'] ?? ''));
        if ($uniform_size !== '') {
            foreach ($fees['uniform_' . $uniform_type] as $item) {
                if ((string) $item->uniform_size === $uniform_size) { $uniform = $item; break; }
            }
            if (!$uniform) return ['ok' => false, 'message' => 'The selected uniform size is not available.'];
        }

        $lines = [];
        foreach ($fees['tuition'] as $item) $lines[] = $this->fee_line('tuition', $item->id, $item->payment_sequence, $item->payment_label, $item->payment_date, $item->tuition_fee);
        foreach ($fees['worktext'] as $item) $lines[] = $this->fee_line('worktext', $item->id, $item->payment_sequence, $item->payment_label, $item->payment_date, $item->amount);
        foreach ($fees['extra_curricular'] as $item) $lines[] = $this->fee_line('extra_curricular', $item->id, $item->payment_sequence, $item->payment_label, $item->payment_date, $item->amount);
        if ($uniform) {
            $top_label = $uniform_type === 'girls' ? 'Blouse' : 'Polo';
            $bottom_label = $uniform_type === 'girls' ? 'Skirt' : 'Pants';
            $lines[] = $this->fee_line('uniform', $uniform->id, null, ucfirst($uniform_type) . ' uniform ' . $top_label . ' - ' . $uniform->uniform_size, null, $uniform->top_price);
            $lines[] = $this->fee_line('uniform', $uniform->id, null, ucfirst($uniform_type) . ' uniform ' . $bottom_label . ' - ' . $uniform->uniform_size, null, $uniform->bottom_price);
        }

        $result = $this->CI->enrollment_model->update_enrollment_assessment(
            (int) $student_id,
            (int) $enrollment_id,
            [
                'academic_year' => $year,
                'grade_level' => $grade,
                'payment_mode' => $payment_mode,
                'fee_configuration_id' => (int) $configuration->id
            ],
            $lines,
            $this->CI->session->userdata('employee_no')
        );
        $messages = [
            'has_payments' => 'This enrollment cannot be edited because a payment has already been recorded.',
            'duplicate_enrollment' => 'This student already has an enrollment record for the selected school year.',
            'enrollment_not_found' => 'The active enrollment record was not found.',
            'assessment_save_failed' => 'The fee assessment could not be updated.',
            'save_failed' => 'The enrollment could not be updated.'
        ];
        if (!$result['ok']) return ['ok' => false, 'message' => $messages[$result['code']] ?? 'The enrollment could not be updated.'];
        return ['ok' => true, 'enrollment_id' => $result['enrollment_id'], 'message' => 'Enrollment and fee assessment updated.'];
    }

    /** Validate forward-only academic-year and grade progression rules. */
    public function validate_placement($student, $target_year, $target_grade, $allow_current_year_setup = false)
    {
        $current_year = trim((string) ($student->academic_year ?? ''));
        $current_grade = trim((string) ($student->grade_level ?? ''));
        if ($current_year === '') return ['ok' => true];

        $current_start = $this->school_year_start($current_year);
        $target_start = $this->school_year_start($target_year);
        if ($current_start === null || $target_start === null) return ['ok' => true];
        if ($target_start < $current_start) {
            return ['ok' => false, 'message' => 'The target school year must be later than the student\'s current enrollment year.'];
        }

        $current_rank = $this->grade_rank($current_grade);
        $target_rank = $this->grade_rank($target_grade);
        if ($target_start === $current_start) {
            if (!$allow_current_year_setup) {
                return ['ok' => false, 'message' => 'The student already has an enrollment for this school year. Choose a later school year.'];
            }
            if ($current_grade !== '' && $target_grade !== $current_grade) {
                return ['ok' => false, 'message' => 'A student\'s grade cannot be changed within the same school year.'];
            }
            return ['ok' => true];
        }

        if ($current_rank !== null && $target_rank !== null && $target_rank < $current_rank) {
            return ['ok' => false, 'message' => 'The target grade cannot be lower than the student\'s current grade. The same grade is allowed for a repeat year.'];
        }
        return ['ok' => true];
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
        if ($grade === 'N') return 0;
        if ($grade === 'K') return 1;
        if (ctype_digit($grade)) return (int) $grade + 1;
        return null;
    }

    private function fee_line($category, $source_id, $sequence, $label, $date, $amount)
    {
        return [
            'fee_category' => $category,
            'source_fee_id' => $source_id,
            'payment_sequence' => $sequence,
            'payment_label' => (string) $label,
            'payment_date' => $date ?: null,
            'amount' => (float) $amount
        ];
    }
}
