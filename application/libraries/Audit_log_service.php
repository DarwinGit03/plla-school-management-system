<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->model(
            'system/Audit_log_model'
        );

        $this->CI->load->library('session');
    }

    public function log(
        $module,
        $action,
        $record_id = null,
        $description = null,
        $details = null
    ) {
        $module = $this->normalize_module($module);
        $action = $this->normalize_action($action);
        $details = $this->decode_details($details);
        $recordContext = $this->resolve_record_context($module, $action, $record_id, $details);
        if ($recordContext) {
            if (!is_array($details)) $details = [];
            $details['_record_context'] = $recordContext;
            $description = $this->humanize_description($description, $recordContext);
        }
        $details = $this->sanitize_details($details);
        $data = [
            'user_id' =>
                $this->CI
                    ->session
                    ->userdata('user_id'),

            'employee_no' =>
                $this->CI
                    ->session
                    ->userdata('employee_no'),

            'session_id' =>
                $this->CI
                    ->session
                    ->userdata('session_token'),

            'module' =>
                $module,

            'action' =>
                $action,

            'record_id' =>
                $record_id,

            'description' =>
                trim((string) $description) !== '' ? trim((string) $description) : $action . ' in ' . $module . '.',

            'details' =>
                $details !== null
                    ? json_encode($details)
                    : null,

            'ip_address' =>
                $this->CI
                    ->input
                    ->ip_address(),

            'user_agent' =>
                $this->CI
                    ->input
                    ->user_agent()
        ];

        return $this->CI
            ->Audit_log_model
            ->create($data);
    }

    /** Resolve record IDs to stable, readable identifiers for audit investigation. */
    public function get_record_context($module, $action, $recordId, $details = null)
    {
        $details = $this->decode_details($details);
        $moduleKey = strtolower(str_replace([' ', '-'], ['_', '_'], trim((string) $module)));
        $actionKey = strtoupper(trim((string) $action));
        $needsAttendanceResolution = strpos($moduleKey, 'attendance') !== false
            && ($actionKey === 'ATTENDANCE_RECORDED' || (is_array($details) && !empty($details['record_id'])));
        $needsGradeResolution = strpos($moduleKey, 'grades') !== false
            && strpos($actionKey, 'CORRECTION') !== false
            && is_array($details) && !empty($details['record_id']);
        if (!$needsAttendanceResolution && !$needsGradeResolution && is_array($details) && !empty($details['_record_context']) && is_array($details['_record_context'])) {
            return $details['_record_context'];
        }
        return $this->resolve_record_context($module, $action, $recordId, $details);
    }

    public function humanize_description($description, array $context = [])
    {
        $description = trim((string) $description);
        $description = preg_replace('/\s*\(LRN\s+[^)]+\)/i', '', $description);
        if ($description === '' || empty($context['type']) || empty($context['name'])) return $description;
        if ($context['type'] === 'Student') {
            $description = preg_replace('/\bstudent\s+' . preg_quote((string) $context['name'], '/') . '/i', 'the student', $description);
            $description = preg_replace('/' . preg_quote((string) $context['name'], '/') . '/i', 'the student', $description);
            return preg_replace('/\b(?:student(?:\s+enrollment)?|enrollment)\s+#\s*\d+\b/i', 'the student', $description);
        }
        if ($context['type'] === 'Employee') {
            $description = preg_replace('/\bemployee\s+' . preg_quote((string) $context['name'], '/') . '/i', 'the employee', $description);
            $description = preg_replace('/' . preg_quote((string) $context['name'], '/') . '/i', 'the employee', $description);
            $description = preg_replace('/\s*\(employee\s+no\.?\s*' . preg_quote((string) ($context['employee_no'] ?? ''), '/') . '\)/i', '', $description);
            return preg_replace('/\bemployee\s+#\s*\d+\b/i', 'the employee', $description);
        }
        return $description;
    }

    private function resolve_record_context($module, $action, $recordId, $details)
    {
        $details = is_array($details) ? $details : [];
        $moduleKey = strtolower(str_replace([' ', '-'], ['_', '_'], trim((string) $module)));
        $actionKey = strtoupper(trim((string) $action));
        $context = [];
        $db = $this->CI->db;
        $studentId = (int) ($details['student_id'] ?? 0);
        $enrollmentId = (int) ($details['student_enrollment_id'] ?? $details['enrollment_id'] ?? 0);
        $employeeId = (int) ($details['employee_id'] ?? 0);
        if ($employeeId === 0 && in_array($moduleKey, ['account', 'administration'], true)
            && (int) $recordId > 0 && $db->table_exists('employees')) {
            $linkedEmployee = $db->select('id')->where('user_id', (int) $recordId)->get('employees')->row();
            if ($linkedEmployee) $employeeId = (int) $linkedEmployee->id;
        }

        if (strpos($moduleKey, 'attendance') !== false && $actionKey === 'ATTENDANCE_RECORDED'
            && $db->table_exists('employee_subject_assignments') && $db->table_exists('academic_subjects') && $db->table_exists('academic_sections')) {
            $sessionContext = $db->select('subjects.subject_name, subjects.subject_code, assignments.school_year, assignments.grade_code, sections.section, sessions.attendance_date')
                ->from('student_attendance_sessions AS sessions')
                ->join('employee_subject_assignments AS assignments', 'assignments.id = sessions.assignment_id')
                ->join('academic_subjects AS subjects', 'subjects.id = assignments.subject_id')
                ->join('academic_sections AS sections', 'sections.id = sessions.section_id')
                ->where('sessions.id', (int) $recordId)->get()->row();
            if ($sessionContext) {
                return ['type' => 'Class attendance', 'name' => (string) $sessionContext->subject_name,
                    'subject_code' => (string) ($sessionContext->subject_code ?? ''),
                    'school_year' => (string) $sessionContext->school_year,
                    'grade' => 'Grade ' . (string) $sessionContext->grade_code,
                    'section' => (string) $sessionContext->section,
                    'date' => (string) $sessionContext->attendance_date];
            }
        }

        if ($moduleKey === 'students' && in_array($actionKey, ['ENROLLMENT_CREATED', 'ENROLLMENT_UPDATED'], true)) $enrollmentId = (int) $recordId;
        if ($moduleKey === 'students' && !$studentId && !$enrollmentId && (int) $recordId > 0) $studentId = (int) $recordId;
        if ($moduleKey === 'student_enrollments' && !$enrollmentId) $enrollmentId = (int) $recordId;
        if ($enrollmentId > 0 && !$studentId) {
            $row = $db->select('student_id')->where('id', $enrollmentId)->get('student_enrollments')->row();
            if ($row) $studentId = (int) $row->student_id;
        }

        $gradeId = (int) ($details['grade_id'] ?? 0);
        if ($gradeId === 0 && strpos($actionKey, 'CORRECTION') !== false) $gradeId = (int) ($details['record_id'] ?? 0);
        if ($gradeId === 0) $gradeId = (int) $recordId;
        if (strpos($moduleKey, 'grades') !== false && $gradeId > 0 && !$studentId && $db->table_exists('student_subject_grades')) {
            $row = $db->select('enrollments.student_id')->from('student_subject_grades AS grades')
                ->join('student_enrollments AS enrollments', 'enrollments.id = grades.enrollment_id')
                ->where('grades.id', $gradeId)->get()->row();
            if ($row) $studentId = (int) $row->student_id;
        }
        if (strpos($moduleKey, 'attendance') !== false && (int) $recordId > 0 && !$studentId && $db->table_exists('student_attendance')) {
            $attendanceId = (int) ($details['attendance_id'] ?? $details['record_id'] ?? $recordId);
            $row = $db->select('enrollments.student_id')->from('student_attendance AS attendance')
                ->join('student_enrollments AS enrollments', 'enrollments.id = attendance.enrollment_id')
                ->where('attendance.id', $attendanceId)->get()->row();
            if ($row) $studentId = (int) $row->student_id;
        }

        if ($studentId > 0 && $db->table_exists('students')) {
            $student = $db->select('student_no, lrn, first_name, middle_name, last_name')->where('id', $studentId)->get('students')->row();
            if ($student) {
                $name = trim(implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name])));
                $context = ['type' => 'Student', 'name' => $name, 'lrn' => (string) ($student->lrn ?? ''), 'student_no' => (string) ($student->student_no ?? '')];
            }
        }

        if (!$context && $employeeId === 0) {
            if (in_array($moduleKey, ['employees', 'employee', 'teachers'], true)) $employeeId = (int) $recordId;
            elseif (in_array($moduleKey, ['employee_salary_history', 'teacher_salary_history'], true) && (int) $recordId > 0 && $db->table_exists('employee_salary_history')) {
                $row = $db->select('employee_id')->where('id', (int) $recordId)->get('employee_salary_history')->row();
                if ($row) $employeeId = (int) $row->employee_id;
            } elseif (in_array($moduleKey, ['payroll', 'payroll_items'], true) && (int) $recordId > 0 && $db->table_exists('payroll_items')) {
                $row = $db->select('employee_id, employee_name_snapshot, employee_no_snapshot')->where('id', (int) $recordId)->get('payroll_items')->row();
                if ($row) {
                    $employeeId = (int) $row->employee_id;
                    $context = ['type' => 'Employee', 'name' => (string) $row->employee_name_snapshot, 'employee_no' => (string) $row->employee_no_snapshot];
                }
            } elseif ($moduleKey === 'academic' && (int) ($details['assignment_id'] ?? 0) > 0) {
                $row = $db->select('employee_id')->where('id', (int) $details['assignment_id'])->get('employee_subject_assignments')->row();
                if ($row) $employeeId = (int) $row->employee_id;
            }
        }
        if (!$context && $employeeId > 0 && $db->table_exists('employees')) {
            $employee = $db->select('employee_no, first_name, middle_name, last_name')->where('id', $employeeId)->get('employees')->row();
            if ($employee) {
                $name = trim(implode(' ', array_filter([$employee->first_name, $employee->middle_name, $employee->last_name])));
                $context = ['type' => 'Employee', 'name' => $name, 'employee_no' => (string) $employee->employee_no];
            }
        }

        if (!$context && in_array($moduleKey, ['academic_subjects', 'academic'], true) && (int) $recordId > 0) {
            $subjectId = (int) ($details['subject_id'] ?? 0);
            if ($subjectId === 0 && $moduleKey === 'academic_subjects') $subjectId = (int) $recordId;
            if ($subjectId === 0 && strpos($actionKey, 'ASSIGN') !== false && $db->table_exists('employee_subject_assignments')) {
                $assignment = $db->select('subject_id')->where('id', (int) $recordId)->get('employee_subject_assignments')->row();
                if ($assignment) $subjectId = (int) $assignment->subject_id;
            }
            if ($subjectId > 0 && $db->table_exists('academic_subjects')) {
                $subject = $db->select('subject_name, subject_code, school_year, grade_code')->where('id', $subjectId)->get('academic_subjects')->row();
                if ($subject) $context = ['type' => 'Subject', 'name' => (string) $subject->subject_name, 'subject_code' => (string) ($subject->subject_code ?? ''), 'school_year' => (string) ($subject->school_year ?? ''), 'grade' => (string) ($subject->grade_code ?? '')];
            }
        }

        if (!$context && in_array($moduleKey, ['academic_sections', 'sections'], true) && (int) $recordId > 0 && $db->table_exists('academic_sections')) {
            $section = $db->select('section, year, grade')->where('id', (int) $recordId)->get('academic_sections')->row();
            if ($section) $context = ['type' => 'Section', 'name' => (string) $section->section, 'school_year' => (string) $section->year, 'grade' => (string) $section->grade];
        }
        if (!$context && in_array($moduleKey, ['events', 'school_events'], true) && (int) $recordId > 0 && $db->table_exists('school_events')) {
            $event = $db->select('title, starts_at')->where('id', (int) $recordId)->get('school_events')->row();
            if ($event) $context = ['type' => 'Event', 'name' => (string) $event->title, 'starts_at' => (string) $event->starts_at];
        }
        if (!$context && in_array($moduleKey, ['administration', 'account'], true) && !empty($details['username'])) {
            $context = ['type' => 'Account', 'name' => '@' . (string) $details['username']];
        }
        if (!$context && !empty($details['school_year'])) {
            $context = ['type' => 'School year', 'name' => (string) $details['school_year']];
        }
        return $context;
    }

    private function decode_details($details)
    {
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $details;
        }
        if (is_object($details)) return (array) $details;
        return $details;
    }

    /** Keep stored module labels consistent even when callers use table or route names. */
    private function normalize_module($module)
    {
        $key = strtolower(trim((string) $module));
        $key = str_replace(['\\', '-', ' '], ['/', '_', '_'], $key);
        $aliases = [
            'auth' => 'Authentication', 'auth_service/login' => 'Authentication',
            'auth/login' => 'Authentication', 'auth/forgot-password' => 'Authentication',
            'auth/verify-otp' => 'Authentication', 'auth/reset-password' => 'Authentication',
            'administration' => 'Administration', 'account' => 'Account',
            'events' => 'Events', 'event' => 'Events',
            'teachers' => 'Employees', 'teacher' => 'Employees', 'employees' => 'Employees',
            'employee_salary_history' => 'Payroll', 'payroll_items' => 'Payroll',
            'payroll_deductions' => 'Payroll', 'payroll_periods' => 'Payroll',
            'payroll_deduction_settings' => 'Payroll', 'payroll_deduction_policies' => 'Payroll',
            'teacher_salary_history' => 'Payroll',
            'academic_sections' => 'Academic', 'sections' => 'Academic',
            'academic_subjects' => 'Academic', 'academic_grades' => 'Academic',
            'employee_subject_assignments' => 'Academic', 'section_assignments' => 'Academic',
            'student_enrollments' => 'Students', 'students' => 'Students',
            'finance' => 'Finance', 'fee_configurations' => 'Finance',
            'payments' => 'Finance', 'student_payments' => 'Finance'
        ];
        if (isset($aliases[$key])) return $aliases[$key];
        return ucwords(str_replace('_', ' ', $key));
    }

    /** Store a predictable verb for common CRUD events; specialized events stay explicit. */
    private function normalize_action($action)
    {
        $key = strtoupper(trim((string) $action));
        $aliases = [
            'CREATE' => 'CREATED', 'INSERT' => 'CREATED', 'USER_CREATED' => 'CREATED',
            'ROLE_CREATED' => 'CREATED', 'TEACHER_CREATED' => 'CREATED',
            'SECTION_CREATED' => 'CREATED', 'SUBJECT_CREATED' => 'CREATED', 'EVENT_CREATED' => 'CREATED',
            'UPDATE' => 'UPDATED', 'USER_ACCESS_UPDATED' => 'UPDATED', 'ROLE_UPDATED' => 'UPDATED',
            'TEACHER_UPDATED' => 'UPDATED', 'SECTION_UPDATED' => 'UPDATED',
            'SUBJECT_UPDATED' => 'UPDATED', 'EVENT_UPDATED' => 'UPDATED', 'PROFILE_UPDATED' => 'UPDATED',
            'DELETE' => 'DELETED', 'REMOVE' => 'DELETED',
            'TEACHER_SALARY_RATE_ADDED' => 'CREATED', 'PAYROLL_PERIOD_CREATED' => 'CREATED',
            'TEACHER_STATUS_CHANGED' => 'STATUS_CHANGED', 'SECTION_STATUS_CHANGED' => 'STATUS_CHANGED',
            'SUBJECT_STATUS_CHANGED' => 'STATUS_CHANGED', 'ASSIGNMENT_STATUS_CHANGED' => 'STATUS_CHANGED',
            'STATUS_CHANGE' => 'STATUS_CHANGED', 'CREATE_STUDENT' => 'CREATED',
            'EVENT_CANCELLED' => 'CANCELLED', 'PAYROLL_DEDUCTION_ADDED' => 'CREATED',
            'PAYROLL_DEDUCTION_REMOVED' => 'DELETED',
            'ACCOUNT_LOCKED' => 'ACCOUNT_LOCKED', 'ACCOUNT_UNLOCKED' => 'ACCOUNT_UNLOCKED',
            'PASSWORD_RESET_REQUEST' => 'PASSWORD_RESET_REQUESTED',
            'STUDENT_SUBJECT_GRADES_SAVED' => 'GRADES_UPDATED',
            'PAYROLL_DEDUCTION_POLICY_CREATED' => 'CREATED',
            'TEACHER_SUBJECT_ASSIGNED' => 'ASSIGNED', 'SECTION_ASSIGNED' => 'ASSIGNED'
        ];
        return $aliases[$key] ?? $key;
    }

    /** Remove credential material before JSON encoding audit details. */
    private function sanitize_details($details)
    {
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            if (json_last_error() === JSON_ERROR_NONE) $details = $decoded;
        }
        if (is_object($details)) $details = (array) $details;
        if (!is_array($details)) return $details;
        $safe = [];
        foreach ($details as $key => $value) {
            if (preg_match('/password|passwd|otp|token|secret|credential|authorization|auth_code|hash/i', (string) $key)) continue;
            $safe[$key] = is_array($value) || is_object($value) ? $this->sanitize_details($value) : $value;
        }
        return $safe;
    }


    public function get_changed_fields(
        $old,
        $new
    ) {
        $changes = [];

        if (!$old || !$new) {
            return $changes;
        }

        $ignored_fields = [
            'id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'deleted_at'
        ];

        if (is_object($new)) {
            $new = (array) $new;
        }

        foreach ($new as $field => $new_value) {

            if (
                in_array(
                    $field,
                    $ignored_fields,
                    true
                )
            ) {
                continue;
            }

            $old_value =
                $old->$field ?? null;

            if (
                (string) $old_value !==
                (string) $new_value
            ) {
                $changes[$field] = [
                    'old' => $old_value,
                    'new' => $new_value
                ];
            }
        }

        return $changes;
    }


    public function get_changed_records(
        $old_records,
        $new_records
    ) {
        $changes = [];

        $old_by_id = [];

        foreach ($old_records as $record) {

            $old_by_id[(int) $record->id] =
                $record;
        }

        $new_by_id = [];

        foreach ($new_records as $record) {

            $new_by_id[(int) $record->id] =
                $record;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE / DELETE
        |--------------------------------------------------------------------------
        */

        foreach ($old_by_id as $id => $old) {

            if (isset($new_by_id[$id])) {

                $field_changes =
                    $this->get_changed_fields(
                        $old,
                        $new_by_id[$id]
                    );

                if (!empty($field_changes)) {

                    $changes[$id] = [
                        'action' => 'UPDATE',
                        'changes' => $field_changes
                    ];
                }

            } else {

                $changes[$id] = [
                    'action' => 'DELETE',
                    'old' => (array) $old
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        foreach ($new_by_id as $id => $new) {

            if (!isset($old_by_id[$id])) {

                $changes[$id] = [
                    'action' => 'CREATE',
                    'new' => (array) $new
                ];
            }
        }

        return $changes;
    }

}
