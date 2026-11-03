<section class="card border-0 shadow-sm">
    <?php
    $actionLabels = [
        'CREATED' => 'Created', 'UPDATED' => 'Updated', 'DELETED' => 'Deleted',
        'ASSIGNED' => 'Assigned', 'STATUS_CHANGED' => 'Status changed',
        'PAYMENT_RECORDED' => 'Payment recorded', 'PAYMENT_VOIDED' => 'Payment voided',
        'PAYMENT_CORRECTED' => 'Payment corrected', 'PASSWORD_CHANGED' => 'Password changed',
        'PASSWORD_RESET' => 'Password reset', 'PASSWORD_RESET_REQUESTED' => 'Password reset requested',
        'OTP_VERIFIED' => 'Verification code confirmed', 'ACCOUNT_LOCKED' => 'Account locked',
        'ACCOUNT_UNLOCKED' => 'Account unlocked', 'LOGOUT' => 'Signed out', 'LOGIN' => 'Signed in',
        'ATTENDANCE_UPDATED' => 'Attendance updated', 'GRADES_UPDATED' => 'Grades updated',
        'FINALIZED' => 'Payroll finalized', 'PAYMENT_MARKED_PAID' => 'Payment marked paid',
        'EMAIL_SENT' => 'Email sent', 'EVENT_CANCELLED' => 'Event cancelled', 'CANCELLED' => 'Cancelled',
        'USER_CREATED' => 'Created', 'ROLE_CREATED' => 'Created', 'TEACHER_CREATED' => 'Created',
        'SECTION_CREATED' => 'Created', 'SUBJECT_CREATED' => 'Created', 'EVENT_CREATED' => 'Created',
        'CREATE_STUDENT' => 'Created',
        'USER_ACCESS_UPDATED' => 'Updated', 'ROLE_UPDATED' => 'Updated', 'TEACHER_UPDATED' => 'Updated',
        'SECTION_UPDATED' => 'Updated', 'SUBJECT_UPDATED' => 'Updated', 'EVENT_UPDATED' => 'Updated',
        'PROFILE_UPDATED' => 'Updated', 'TEACHER_STATUS_CHANGED' => 'Status changed',
        'SECTION_STATUS_CHANGED' => 'Status changed', 'SUBJECT_STATUS_CHANGED' => 'Status changed',
        'ASSIGNMENT_STATUS_CHANGED' => 'Status changed', 'STATUS_CHANGE' => 'Status changed', 'TEACHER_SUBJECT_ASSIGNED' => 'Assigned',
        'SECTION_ASSIGNED' => 'Assigned', 'TEACHER_SALARY_RATE_ADDED' => 'Created',
        'PAYROLL_DEDUCTION_POLICY_CREATED' => 'Created', 'PAYROLL_PERIOD_CREATED' => 'Created',
        'PAYROLL_DEDUCTION_ADDED' => 'Created',
        'STUDENT_SUBJECT_GRADES_SAVED' => 'Grades updated', 'PAYSLIP_EMAILED' => 'Payslip emailed',
        'PAYROLL_FINALIZED' => 'Payroll finalized', 'PAYMENT_MARKED_PAID' => 'Payment marked paid',
        'PAYROLL_DEDUCTION_REMOVED' => 'Deduction removed',
        'SCHOOL_YEAR_DUPLICATED' => 'School year duplicated', 'SCHOOL_YEAR_DELETED' => 'School year deleted',
        'SCHOOL_YEAR_STATUS_CHANGED' => 'School year status changed'
    ];
    $moduleLabels = [
        'auth' => 'Authentication', 'auth_service/login' => 'Authentication',
        'auth/login' => 'Authentication', 'auth/forgot-password' => 'Authentication',
        'auth/verify-otp' => 'Authentication', 'auth/reset-password' => 'Authentication',
        'teachers' => 'Employees', 'teacher' => 'Employees', 'payroll_items' => 'Payroll',
        'teacher_salary_history' => 'Payroll', 'payroll_deduction_settings' => 'Payroll',
        'payroll_deductions' => 'Payroll', 'payroll_periods' => 'Payroll',
        'academic_sections' => 'Academic', 'academic_subjects' => 'Academic',
        'academic_grades' => 'Academic', 'employee_subject_assignments' => 'Academic',
        'student_enrollments' => 'Students'
    ];
    $auditModals = [];
    ?>
    <div class="card-header bg-white">
        <form method="get" action="<?= site_url('admin/audit-logs'); ?>" class="row g-2 align-items-end">
            <div class="col-12 col-lg-5"><label for="auditSearch" class="form-label mb-1">Search audit activity</label><input id="auditSearch" name="q" class="form-control" value="<?= html_escape($search); ?>" placeholder="Name, username, module, action, description, or IP"></div>
            <div class="col-6 col-lg-2"><label for="auditDateFrom" class="form-label mb-1">From</label><input id="auditDateFrom" type="date" name="date_from" class="form-control" value="<?= html_escape($date_from); ?>"></div>
            <div class="col-6 col-lg-2"><label for="auditDateTo" class="form-label mb-1">To</label><input id="auditDateTo" type="date" name="date_to" class="form-control" value="<?= html_escape($date_to); ?>"></div>
            <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Filter</button></div>
            <div class="col-auto"><a class="btn btn-outline-secondary" href="<?= site_url('admin/audit-logs'); ?>">Clear</a></div>
            <div class="col text-md-end small text-secondary"><?= number_format($total_logs); ?> events</div>
        </form>
        <?php if (!empty($date_error)): ?><div class="small text-danger mt-2"><?= html_escape($date_error); ?></div><?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">Date and time</th><th>Who</th><th>Module</th><th>Action</th><th>What happened</th><th>Investigation</th></tr></thead>
            <tbody>
            <?php if ($logs): foreach ($logs as $log): ?>
                <?php
                    $actor = trim(($log->first_name ?? '') . ' ' . ($log->last_name ?? ''));
                    $actionCode = strtoupper((string) $log->action);
                    $moduleCode = strtolower((string) $log->module);
                    $actionLabel = $actionLabels[$actionCode] ?? ucwords(strtolower(str_replace('_', ' ', $actionCode)));
                    $moduleLabel = $moduleLabels[$moduleCode] ?? ucwords(str_replace('_', ' ', (string) $log->module));
                    $legacyAuthActions = [
                        'auth/forgot-password' => 'Password reset requested',
                        'auth/verify-otp' => 'Verification code confirmed',
                        'auth/reset-password' => 'Password reset',
                        'auth/login' => 'Account locked',
                        'auth_service/login' => 'Account unlocked'
                    ];
                    $legacyAuthEvent = $legacyAuthActions[strtolower((string) $log->action)] ?? null;
                    $isLegacyAuthLog = $legacyAuthEvent !== null && ctype_digit((string) $log->module);
                    if ($isLegacyAuthLog) {
                        $moduleLabel = 'Authentication';
                        $actionLabel = $legacyAuthEvent;
                    }
                    $decodedDetails = json_decode((string) ($log->details ?? ''), true);
                    $details = json_last_error() === JSON_ERROR_NONE ? $decodedDetails : ($log->details ?? '');
                    if (is_array($details)) unset($details['_record_context']);
                    $detailsText = is_array($details)
                        ? json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        : (is_string($details) ? $details : (string) ($log->details ?? ''));
                    $actorFallback = !empty($log->employee_no) ? 'Employee ' . $log->employee_no : (!empty($log->user_id) ? 'User #' . (int) $log->user_id : 'Unauthenticated request');
                    $eventDescription = $log->display_description ?? ($log->description ?: ($isLegacyAuthLog && is_string($details) ? $details : '—'));
                    $recordContext = $isLegacyAuthLog ? [] : (is_array($log->record_context ?? null) ? $log->record_context : []);
                    $contextTitle = trim((string) ($recordContext['type'] ?? '') . ': ' . (string) ($recordContext['name'] ?? ''));
                    $showContextTitle = $contextTitle !== ':'
                        && !(($recordContext['type'] ?? '') === 'Class attendance' && !empty($recordContext['name']) && stripos($eventDescription, (string) $recordContext['name']) !== false);
                    $contextMeta = [];
                    if (!empty($recordContext['student_no'])) $contextMeta[] = 'Student no: ' . $recordContext['student_no'];
                    if (!empty($recordContext['employee_no'])) $contextMeta[] = 'Employee no: ' . $recordContext['employee_no'];
                    if (!empty($recordContext['subject_code'])) $contextMeta[] = 'Code ' . $recordContext['subject_code'];
                    if (!empty($recordContext['grade'])) $contextMeta[] = $recordContext['grade'];
                    if (!empty($recordContext['school_year'])) $contextMeta[] = $recordContext['school_year'];
                    if (!empty($recordContext['section'])) $contextMeta[] = 'Section ' . $recordContext['section'];
                    if (!empty($recordContext['date'])) $contextMeta[] = $recordContext['date'];
                    if (!empty($recordContext['starts_at'])) $contextMeta[] = $recordContext['starts_at'];
                    $contextMetaToShow = array_values(array_filter($contextMeta, function ($item) use ($eventDescription) {
                        return stripos($eventDescription, $item) === false;
                    }));
                    $targetAccount = $isLegacyAuthLog ? (int) $log->module : null;
                    $modalId = 'auditEvent' . (int) $log->id;
                ?>
                <tr>
                    <td class="ps-3 text-nowrap"><?= html_escape($log->created_at); ?></td>
                    <td><?= html_escape($actor ?: $actorFallback); ?>
                        <?php if (!empty($log->username)): ?><div class="small text-secondary">@<?= html_escape($log->username); ?></div><?php endif; ?>
                        <?php if (!empty($log->employee_no)): ?><div class="small text-secondary">Employee no. <?= html_escape($log->employee_no); ?></div><?php endif; ?>
                    </td>
                    <td><?= html_escape($moduleLabel); ?></td>
                    <td><span class="badge bg-light text-dark border"><?= html_escape($actionLabel); ?></span></td>
                    <td><?= html_escape($eventDescription); ?><?php if ($showContextTitle || $contextMetaToShow): ?><?php if ($showContextTitle): ?><div class="small fw-semibold mt-1"><?= html_escape($contextTitle); ?></div><?php endif; ?><?php if ($contextMetaToShow): ?><div class="small text-secondary"><?= html_escape(implode(' · ', $contextMetaToShow)); ?></div><?php endif; ?><?php endif; ?></td>
                    <td><button type="button" class="btn btn-sm btn-link text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#<?= html_escape($modalId); ?>"><i class="fas fa-eye me-1" aria-hidden="true"></i>View</button></td>
                </tr>
                <?php ob_start(); ?>
                <div class="modal fade" id="<?= html_escape($modalId); ?>" tabindex="-1" aria-labelledby="<?= html_escape($modalId); ?>Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div><h5 class="modal-title" id="<?= html_escape($modalId); ?>Label">Activity details</h5><div class="small text-secondary"><?= html_escape($log->created_at); ?></div></div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6"><div class="small text-secondary">Who</div><div class="fw-semibold"><?= html_escape($actor ?: $actorFallback); ?></div><?php if (!empty($log->username)): ?><div class="small">@<?= html_escape($log->username); ?></div><?php endif; ?><?php if (!empty($log->employee_no)): ?><div class="small">Employee no. <?= html_escape($log->employee_no); ?></div><?php endif; ?></div>
                                    <div class="col-sm-3"><div class="small text-secondary">Module</div><div class="fw-semibold"><?= html_escape($moduleLabel); ?></div></div>
                                    <div class="col-sm-3"><div class="small text-secondary">Action</div><div class="fw-semibold"><?= html_escape($actionLabel); ?></div></div>
                                    <?php if ($targetAccount): ?><div class="col-sm-6"><div class="small text-secondary">Target account</div><div>Account #<?= (int) $targetAccount; ?></div></div><?php endif; ?>
                                    <?php if ($showContextTitle || $contextMetaToShow): ?><div class="col-sm-6"><div class="small text-secondary">Affected</div><?php if ($showContextTitle): ?><div class="fw-semibold"><?= html_escape($contextTitle); ?></div><?php endif; ?><?php if ($contextMetaToShow): ?><div class="small text-secondary"><?= html_escape(implode(' · ', $contextMetaToShow)); ?></div><?php endif; ?></div><?php endif; ?>
                                    <div class="col-12"><div class="small text-secondary">Description</div><div><?= html_escape($eventDescription); ?></div></div>
                                </div>
                                <?php if ($detailsText !== ''): ?><div class="mb-3"><div class="small text-secondary mb-1">Change details</div><pre class="bg-light border rounded p-3 mb-0" style="white-space:pre-wrap;word-break:break-word"><?= html_escape($detailsText); ?></pre></div><?php endif; ?>
                                <div class="row g-3 border-top pt-3">
                                    <div class="col-sm-6"><div class="small text-secondary">IP address</div><code><?= html_escape($log->ip_address ?: 'Not recorded'); ?></code></div>
                                    <div class="col-sm-6"><div class="small text-secondary">Session reference</div><code><?= !empty($log->session_id) ? html_escape(substr((string) $log->session_id, 0, 8) . '…') : 'Not recorded'; ?></code></div>
                                    <div class="col-12"><div class="small text-secondary">Browser / device</div><div class="small text-break"><?= html_escape($log->user_agent ?: 'Not recorded'); ?></div></div>
                                </div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
                        </div>
                    </div>
                </div>
                <?php $auditModals[] = ob_get_clean(); ?>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center text-secondary py-5">No audit events match this search.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= implode("\n", $auditModals); ?>
    <?php $logParams = array_filter(['q' => $search, 'date_from' => $date_from, 'date_to' => $date_to], function ($value) { return $value !== ''; }); ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center"><small class="text-secondary">Page <?= (int) $current_page; ?> of <?= (int) $total_pages; ?></small><div class="btn-group btn-group-sm"><?php $logParams['page'] = max(1, $current_page - 1); ?><a class="btn btn-outline-secondary <?= $current_page <= 1 ? 'disabled' : ''; ?>" href="<?= html_escape(site_url('admin/audit-logs') . '?' . http_build_query($logParams)); ?>">Previous</a><?php $logParams['page'] = min($total_pages, $current_page + 1); ?><a class="btn btn-outline-secondary <?= $current_page >= $total_pages ? 'disabled' : ''; ?>" href="<?= html_escape(site_url('admin/audit-logs') . '?' . http_build_query($logParams)); ?>">Next</a></div></div>
</section>
<script>
    (function () {
        var fromDate = document.getElementById('auditDateFrom');
        var toDate = document.getElementById('auditDateTo');
        if (!fromDate || !toDate) return;
        fromDate.addEventListener('change', function () {
            if (this.value) toDate.value = this.value;
        });
    })();
</script>
