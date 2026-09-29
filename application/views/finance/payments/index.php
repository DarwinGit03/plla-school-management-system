<div class="container-fluid">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show js-auto-dismiss" role="alert">
            <?= html_escape($this->session->flashdata('success')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show js-auto-dismiss" role="alert">
            <?= html_escape($this->session->flashdata('error')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
        <div class="form-text">All years shows a separate balance for each student enrollment.</div>
            <form method="get" action="<?= site_url('finance/payments'); ?>" class="js-finance-payment-search" data-grade-options="<?= html_escape(json_encode($grade_options_by_year)); ?>" data-section-url="<?= site_url('students/sections'); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="search" class="form-label">Search for a student</label>
                        <input
                            id="search"
                            class="form-control"
                            type="search"
                            name="search"
                            value="<?= html_escape($search); ?>"
                            placeholder="Name, student number, or LRN (optional with filters)">
                    </div>
                    <div class="col-12 col-md-3 col-lg-2">
                        <label for="academic_year" class="form-label">School year</label>
                        <select id="academic_year" name="academic_year" class="form-select">
                            <option value="" <?= $academic_year === '' ? 'selected' : ''; ?>>All school years</option>
                            <?php foreach ($school_years as $year): ?>
                                <option value="<?= html_escape($year->school_year); ?>" <?= $academic_year === (string) $year->school_year ? 'selected' : ''; ?>>
                                    <?= html_escape($year->school_year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-2">
                        <label for="grade_level" class="form-label">Grade</label>
                        <select id="grade_level" name="grade_level" class="form-select" <?= $academic_year === '' ? 'disabled' : ''; ?>>
                            <option value="">All grades</option>
                            <?php foreach ($grade_levels as $grade): ?>
                                <?php
                                    $grade_value = (string) $grade->grade;
                                    $grade_label = strcasecmp($grade_value, 'N') === 0
                                        ? 'Nursery'
                                        : (strcasecmp($grade_value, 'K') === 0
                                            ? 'Kindergarten'
                                            : (stripos($grade_value, 'grade') === 0 ? $grade_value : 'Grade ' . $grade_value));
                                ?>
                                <option value="<?= html_escape($grade_value); ?>" <?= $grade_level === $grade_value ? 'selected' : ''; ?>><?= html_escape($grade_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-2">
                        <label for="section" class="form-label">Section</label>
                        <select id="section" name="section" class="form-select" <?= ($academic_year === '' || $grade_level === '') ? 'disabled' : ''; ?>>
                            <option value="">All sections</option>
                            <?php foreach ($sections as $section_option): ?>
                                <option value="<?= html_escape($section_option->section); ?>" <?= $section === (string) $section_option->section ? 'selected' : ''; ?>><?= html_escape($section_option->section); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-2 d-flex gap-2 align-items-end">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1" aria-hidden="true"></i>
                            Search
                        </button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('finance/payments'); ?>">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$has_search): ?>
        <div class="alert alert-info" role="status">
            Enter a student name, or select a school year and grade to list matching enrollments. Each enrollment has its own balance and due dates.
        </div>
    <?php elseif ($student_id > 0): ?>
        <?php if (!$selected_student): ?>
            <div class="alert alert-warning" role="alert">
                This student record could not be found.
                <a href="<?= site_url('finance/payments?' . http_build_query(['search' => $search, 'academic_year' => $academic_year, 'grade_level' => $grade_level, 'section' => $section])); ?>" class="alert-link">Return to search results</a>.
            </div>
        <?php else: ?>
            <?php $selected_name = trim($selected_student->first_name . ' ' . $selected_student->middle_name . ' ' . $selected_student->last_name . ' ' . $selected_student->suffix); ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <div class="small text-muted">Selected student</div>
                        <h2 class="h5 fw-bold mb-1"><?= html_escape($selected_name); ?></h2>
                        <div class="text-muted">
                            Student No. <?= html_escape($selected_student->student_no); ?>
                            <?php if (!empty($selected_student->lrn)): ?>
                                <span class="mx-1">·</span>LRN <?= html_escape($selected_student->lrn); ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($selected_student->enrollments)): ?>
                            <div class="small text-muted mt-1"><?= html_escape($selected_student->enrollments); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                        <div class="text-lg-end">
                            <div class="small text-muted">Total assessed</div>
                            <div class="h5 fw-bold mb-0">₱<?= number_format((float) $selected_student->total_assessed, 2); ?></div>
                        </div>
                        <div class="text-lg-end">
                            <div class="small text-muted">Total paid</div>
                            <div class="h5 fw-bold text-success mb-0">₱<?= number_format((float) $selected_student->total_paid, 2); ?></div>
                        </div>
                        <div class="text-lg-end">
                            <div class="small text-muted">Remaining balance</div>
                            <div class="h4 fw-bold mb-0">₱<?= number_format((float) $selected_student->balance, 2); ?></div>
                        </div>
                        <a
                            class="btn btn-outline-secondary"
                            href="<?= site_url('finance/payments?' . http_build_query(['search' => $search, 'academic_year' => $academic_year, 'grade_level' => $grade_level, 'section' => $section])); ?>">
                            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>
                            Back to results
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($fee_balances)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <strong>Outstanding fee items</strong>
                        <span class="small text-muted"><?= number_format(count($fee_balances)); ?> items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">Category</th>
                                    <th>Payment item</th>
                                    <th>Due date</th>
                                    <th class="text-end">Assessed</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-end px-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fee_balances as $fee): ?>
                                    <tr>
                                        <td class="px-3"><?= html_escape(ucwords(str_replace('_', ' ', $fee->fee_category))); ?></td>
                                        <td><?= html_escape($fee->payment_label); ?></td>
                                        <td><?= !empty($fee->payment_date) ? html_escape($fee->payment_date) : '—'; ?></td>
                                        <td class="text-end">₱<?= number_format((float) $fee->amount_due, 2); ?></td>
                                        <td class="text-end">₱<?= number_format((float) $fee->amount_paid, 2); ?></td>
                                        <td class="text-end fw-semibold">₱<?= number_format((float) $fee->balance, 2); ?></td>
                                        <td class="text-end px-3">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary js-record-payment"
                                                data-bs-toggle="modal"
                                                data-bs-target="#recordPaymentModal"
                                                data-fee-id="<?= (int) $fee->enrollment_fee_id; ?>"
                                                data-category="<?= html_escape(ucwords(str_replace('_', ' ', $fee->fee_category))); ?>"
                                                data-label="<?= html_escape($fee->payment_label); ?>"
                                                data-student-name="<?= html_escape($selected_name); ?>"
                                                data-balance="<?= html_escape(number_format((float) $fee->balance, 2, '.', '')); ?>">
                                                <i class="fas fa-cash-register me-1" aria-hidden="true"></i>
                                                Record payment
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success" role="status">
                    This student has no outstanding assessed fees.
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
                    <div>
                        <strong>Payment history</strong>
                        <span class="small text-muted ms-2"><?= number_format(count($payment_history)); ?> records</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('finance/payments/payment-record-view/' . (int) $selected_student->student_id . ($academic_year !== '' ? '?' . http_build_query(['academic_year' => $academic_year]) : '')); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-eye me-1" aria-hidden="true"></i>View PDF
                        </a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('finance/payments/statement/' . (int) $selected_student->student_id . ($academic_year !== '' ? '?' . http_build_query(['academic_year' => $academic_year]) : '')); ?>">
                            <i class="fas fa-file-pdf me-1" aria-hidden="true"></i>Download PDF
                        </a>
                        <form method="post" action="<?= site_url('finance/payments/email-statement/' . (int) $selected_student->student_id); ?>">
                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                            <input type="hidden" name="search" value="<?= html_escape($search); ?>">
                            <input type="hidden" name="academic_year" value="<?= html_escape($academic_year); ?>">
                            <input type="hidden" name="grade_level" value="<?= html_escape($grade_level); ?>">
                            <input type="hidden" name="section" value="<?= html_escape($section); ?>">
                            <button class="btn btn-sm btn-primary" type="submit" <?= empty($payment_history) ? 'disabled' : ''; ?>>
                                <i class="fas fa-envelope me-1" aria-hidden="true"></i>Send to guardian
                            </button>
                        </form>
                    </div>
                </div>
                <?php if (!empty($payment_history)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">Date</th>
                                    <th>Receipt</th>
                                    <th>School year</th>
                                    <th>Fee item</th>
                                    <th>Payment method</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>
                                    <th>Recorded by</th>
                                    <th class="text-center px-3">Status</th>
                                    <th class="text-end px-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php foreach ($payment_history as $payment): ?>
                                    <?php
                                        $payment_audit_entries = $payment_audit[(int) $payment->id] ?? [];
                                        $void_audit_entry = null;
                                        foreach ($payment_audit_entries as $audit_entry) {
                                            if ($audit_entry->action === 'void') { $void_audit_entry = $audit_entry; break; }
                                        }
                                    ?>
                                    <tr>
                                        <td class="px-3"><?= html_escape(date('M j, Y g:i A', strtotime($payment->paid_at))); ?></td>
                                        <td><?= html_escape($payment->receipt_number ?: '—'); ?></td>
                                        <td><?= html_escape($payment->academic_year); ?></td>
                                        <td>
                                            <span class="d-block"><?= html_escape(ucwords(str_replace('_', ' ', $payment->fee_category))); ?></span>
                                            <span class="small text-muted"><?= html_escape($payment->payment_label); ?></span>
                                        </td>
                                        <td><?= html_escape(ucwords(str_replace('_', ' ', $payment->payment_method))); ?></td>
                                        <td><?= html_escape($payment->reference_number ?: '—'); ?></td>
                                        <td class="text-end fw-semibold">₱<?= number_format((float) $payment->amount_paid, 2); ?></td>
                                        <td><?= html_escape($payment->recorded_by_name ?? 'Unknown user'); ?></td>
                                        <td class="text-center px-3">
                                            <span class="badge <?= $payment->status === 'posted' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?= html_escape(ucwords($payment->status)); ?>
                                            </span>
                                            <?php if ($payment->status === 'voided' && $void_audit_entry): ?>
                                                <div class="small text-muted mt-1">Voided by <?= html_escape($void_audit_entry->performed_by_name); ?> · <?= html_escape($void_audit_entry->performed_at); ?></div>
                                                <div class="small text-muted"><?= html_escape($void_audit_entry->reason); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end px-3 text-nowrap">
                                            <?php if ($payment_audit_entries): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#paymentAuditModal<?= (int) $payment->id; ?>"><i class="fas fa-history me-1" aria-hidden="true"></i>History</button>
                                            <?php endif; ?>
                                            <?php if ($payment->status === 'posted'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-correct-payment" data-bs-toggle="modal" data-bs-target="#correctPaymentModal" data-payment-id="<?= (int) $payment->id; ?>" data-method="<?= html_escape($payment->payment_method); ?>" data-reference="<?= html_escape($payment->reference_number ?? ''); ?>" data-receipt="<?= html_escape($payment->receipt_number); ?>"><i class="fas fa-pen me-1" aria-hidden="true"></i>Correct details</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger js-void-payment" data-bs-toggle="modal" data-bs-target="#voidPaymentModal" data-payment-id="<?= (int) $payment->id; ?>" data-receipt="<?= html_escape($payment->receipt_number); ?>"><i class="fas fa-ban me-1" aria-hidden="true"></i>Void</button>
                                            <?php else: ?>
                                                <span class="small text-muted">No actions</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-body text-muted">No payments have been recorded for this student yet.</div>
                <?php endif; ?>
            </div>
            <?php if (!empty($payment_history)): ?>
                <?php foreach ($payment_history as $payment): ?>
                    <?php if (!empty($payment_audit[(int) $payment->id])): ?>
                        <div class="modal fade" id="paymentAuditModal<?= (int) $payment->id; ?>" tabindex="-1" aria-labelledby="paymentAuditModalLabel<?= (int) $payment->id; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
                                <div class="modal-header"><div><h2 class="modal-title h5 fw-bold" id="paymentAuditModalLabel<?= (int) $payment->id; ?>">Payment change history</h2><div class="small text-muted">Receipt <?= html_escape($payment->receipt_number); ?></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                                <div class="modal-body">
                                    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                                        <thead><tr><th>Change</th><th>Previous value</th><th>New value</th><th>Reason</th><th>Changed by</th><th>Date</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($payment_audit[(int) $payment->id] as $audit): ?>
                                                <tr>
                                                    <td><?= $audit->action === 'void' ? 'Voided' : html_escape(ucwords(str_replace('_', ' ', $audit->field_name))); ?></td>
                                                    <td><?= $audit->action === 'void' ? '—' : html_escape($audit->old_value ?? '(empty)'); ?></td>
                                                    <td><?= $audit->action === 'void' ? '—' : html_escape($audit->new_value ?? '(empty)'); ?></td>
                                                    <td><?= html_escape($audit->reason); ?></td>
                                                    <td><?= html_escape($audit->performed_by_name); ?></td>
                                                    <td class="text-nowrap"><?= html_escape($audit->performed_at); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table></div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
                            </div></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="modal fade" id="voidPaymentModal" tabindex="-1" aria-labelledby="voidPaymentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                        <form method="post" action="<?= site_url('finance/payments/void'); ?>">
                            <div class="modal-header"><div><h2 class="modal-title h5 fw-bold" id="voidPaymentModalLabel">Void payment</h2><div class="small text-muted" id="voidPaymentReceipt"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                <input type="hidden" name="payment_id" id="voidPaymentId"><input type="hidden" name="student_id" value="<?= (int) $selected_student->student_id; ?>">
                                <input type="hidden" name="search" value="<?= html_escape($search); ?>"><input type="hidden" name="academic_year" value="<?= html_escape($academic_year); ?>"><input type="hidden" name="grade_level" value="<?= html_escape($grade_level); ?>"><input type="hidden" name="section" value="<?= html_escape($section); ?>">
                                <div class="alert alert-warning">The amount will be removed from paid totals and balances. The receipt and void reason remain in history.</div>
                                <label for="voidPaymentReason" class="form-label">Reason for voiding</label>
                                <textarea id="voidPaymentReason" name="reason" class="form-control" minlength="5" maxlength="500" rows="3" required></textarea>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Void payment</button></div>
                        </form>
                    </div></div>
                </div>
                <div class="modal fade" id="correctPaymentModal" tabindex="-1" aria-labelledby="correctPaymentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                        <form method="post" action="<?= site_url('finance/payments/correct'); ?>">
                            <div class="modal-header"><div><h2 class="modal-title h5 fw-bold" id="correctPaymentModalLabel">Correct payment details</h2><div class="small text-muted" id="correctPaymentReceipt"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                <input type="hidden" name="payment_id" id="correctPaymentId"><input type="hidden" name="student_id" value="<?= (int) $selected_student->student_id; ?>">
                                <input type="hidden" name="search" value="<?= html_escape($search); ?>"><input type="hidden" name="academic_year" value="<?= html_escape($academic_year); ?>"><input type="hidden" name="grade_level" value="<?= html_escape($grade_level); ?>"><input type="hidden" name="section" value="<?= html_escape($section); ?>">
                                <label for="correctPaymentMethod" class="form-label">Payment method</label>
                                <select id="correctPaymentMethod" name="payment_method" class="form-select mb-3" required>
                                    <option value="">Choose payment method</option>
                                    <?php foreach ($payment_methods as $method => $label): ?><option value="<?= html_escape($method); ?>"><?= html_escape($label); ?></option><?php endforeach; ?>
                                </select>
                                <label for="correctPaymentReference" class="form-label">Reference number</label>
                                <input id="correctPaymentReference" type="text" name="reference_number" class="form-control mb-3" maxlength="100">
                                <label for="correctPaymentReason" class="form-label">Correction reason</label>
                                <textarea id="correctPaymentReason" name="reason" class="form-control" minlength="5" maxlength="500" rows="3" required></textarea>
                                <div class="form-text">Amount, receipt number, and fee item cannot be edited.</div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save correction</button></div>
                        </form>
                    </div></div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif (!empty($students)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <strong>Matching students</strong>
                <span class="small text-muted"><?= number_format(count($students)); ?> records</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Student</th>
                            <th>Student No.</th>
                            <th>LRN</th>
                            <th>Enrollment</th>
                            <th class="text-center">Unpaid items</th>
                            <th class="text-end">Balance</th>
                            <th class="text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php $student_name = trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name . ' ' . $student->suffix); ?>
                            <tr>
                                <td class="px-3 fw-semibold"><?= html_escape($student_name); ?></td>
                                <td><?= html_escape($student->student_no); ?></td>
                                <td><?= html_escape($student->lrn ?: '—'); ?></td>
                                <td><?= html_escape($student->academic_year . ' - ' . $student->grade_level . (!empty($student->section) ? ' - ' . $student->section : '')); ?></td>
                                <td class="text-center"><?= number_format((int) $student->outstanding_items); ?></td>
                                <td class="text-end fw-semibold">₱<?= number_format((float) $student->outstanding_balance, 2); ?></td>
                                <td class="text-end px-3">
                                    <a
                                        class="btn btn-sm btn-outline-primary"
                                        href="<?= site_url('finance/payments?' . http_build_query(['search' => $search, 'student_id' => (int) $student->student_id, 'academic_year' => $student->academic_year, 'grade_level' => $grade_level, 'section' => $section])); ?>">
                                        Payment
                                        <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="status">
            No enrolled students with fee assessments matched “<?= html_escape($search); ?>”.
        </div>
    <?php endif; ?>

    <?php if ($selected_student && !empty($fee_balances)): ?>
        <div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="<?= site_url('finance/payments'); ?>">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title h5 fw-bold" id="recordPaymentModalLabel">Record payment</h2>
                                <div class="small text-muted" id="paymentFeeDescription"></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border d-flex justify-content-between mb-3">
                                <span>Remaining balance</span>
                                <strong id="paymentFeeBalance">₱0.00</strong>
                            </div>
                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                            <input type="hidden" name="enrollment_fee_id" id="paymentFeeId">
                            <input type="hidden" name="student_id" value="<?= (int) $selected_student->student_id; ?>">
                            <input type="hidden" name="search" value="<?= html_escape($search); ?>">
                            <input type="hidden" name="academic_year" value="<?= html_escape($academic_year); ?>">
                            <input type="hidden" name="grade_level" value="<?= html_escape($grade_level); ?>">
                            <input type="hidden" name="section" value="<?= html_escape($section); ?>">
                            <div class="mb-3">
                                <label for="amountPaid" class="form-label">Amount received</label>
                                <input id="amountPaid" type="number" name="amount_paid" class="form-control" min="0.01" step="0.01" required>
                                <div class="form-text">You can record a partial payment up to the remaining balance.</div>
                            </div>
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Payment method</label>
                                <select id="paymentMethod" name="payment_method" class="form-select" required>
                                    <option value="">Choose payment method</option>
                                    <?php foreach ($payment_methods as $method => $label): ?>
                                        <option value="<?= html_escape($method); ?>"><?= html_escape($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="referenceNumber" class="form-label">Reference number <span class="text-muted">(optional)</span></label>
                                <input id="referenceNumber" type="text" name="reference_number" class="form-control" maxlength="100">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1" aria-hidden="true"></i>
                                Save payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
