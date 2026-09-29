<div class="container-fluid">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('success')); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
    <?php endif; ?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Enrollment setup</h1>
            <p class="text-muted mb-0">The current placement is shown for reference. Select the target placement to load its fee schedule.</p>
        </div>
        <a href="<?= site_url('students/enroll'); ?>" class="btn btn-outline-secondary">Back to student search</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Student</div>
                    <div class="fw-semibold fs-5"><?= html_escape(trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name . ' ' . $student->suffix)); ?></div>
                    <div class="text-muted">Student No. <?= html_escape($student->student_no); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Current enrollment</div>
                    <div class="fw-semibold">
                        <?= html_escape($student->academic_year ?: 'Not enrolled'); ?>
                        <?php if (!empty($student->grade_level)): ?>
                            · <?= (stripos(trim($student->grade_level), 'grade ') === 0 ? '' : 'Grade ') . html_escape($student->grade_level); ?>
                        <?php endif; ?>
                        <?php if (!empty($student->section)): ?>
                            · <?= html_escape($student->section); ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($student->academic_year)): ?>
                        <div class="small text-muted mt-1">Choose a later school year to re-enroll. The same grade is allowed for a repeat year, but a lower grade is not.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Target enrollment details</strong>
            <span class="badge text-bg-info"><?= html_escape($enrollment_type); ?></span>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('students/enroll/' . (int) $student->id); ?>" class="js-enrollment-setup">
                <?php if ($admin_edit): ?><input type="hidden" name="edit" value="1"><?php endif; ?>
                <?php if ($initial_flow): ?>
                    <input type="hidden" name="flow" value="initial">
                <?php endif; ?>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="academic_year" class="form-label">Target school year</label>
                        <select class="form-select" name="academic_year" id="academic_year" required>
                            <option value="">Choose school year</option>
                            <?php foreach ($academic_years as $year): ?>
                                <option value="<?= html_escape($year->school_year); ?>" <?= $selected['academic_year'] === (string) $year->school_year ? 'selected' : ''; ?>><?= html_escape($year->school_year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="grade_code" class="form-label">Grade</label>
                        <select class="form-select" name="grade_code" id="grade_code" required <?= empty($selected['academic_year']) ? 'disabled' : ''; ?>>
                            <option value="">Choose grade</option>
                            <?php foreach ($grades as $grade): ?>
                                <option value="<?= html_escape($grade->grade_code); ?>" <?= $selected['grade_code'] === (string) $grade->grade_code ? 'selected' : ''; ?>><?= html_escape($grade->grade_code === 'N' ? 'Nursery' : ($grade->grade_code === 'K' ? 'Kindergarten' : 'Grade ' . $grade->grade_code)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="payment_mode" class="form-label">Payment mode</label>
                        <select class="form-select" name="payment_mode" id="payment_mode" required>
                            <option value="">Choose payment mode</option>
                            <?php foreach ($payment_modes as $mode => $label): ?>
                                <option value="<?= html_escape($mode); ?>" <?= $selected['payment_mode'] === $mode ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($fees && !empty($uniforms)): ?>
                        <div class="col-12 col-md-4">
                            <label for="uniform_size" class="form-label">Uniform size <span class="text-muted">(optional)</span></label>
                            <select class="form-select" name="uniform_size" id="uniform_size">
                                <option value="">Do not include uniform</option>
                                <?php foreach ($uniforms as $uniform): ?>
                                    <option value="<?= html_escape($uniform->uniform_size); ?>" <?= $selected['uniform_size'] === (string) $uniform->uniform_size ? 'selected' : ''; ?>><?= html_escape($uniform->uniform_size); ?> · ₱<?= number_format((float) $uniform->top_price + (float) $uniform->bottom_price, 2); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($enrollment_saved): ?>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Saved fee assessment</h2>
                <div class="text-muted small"><?= html_escape($selected['academic_year']); ?> · Grade <?= html_escape($selected['grade_code']); ?> · <?= html_escape($payment_modes[$selected['payment_mode']]); ?></div>
            </div>
            <div class="text-md-end">
                <div class="small text-muted">Snapshot total</div>
                <div class="h4 fw-bold mb-0">₱<?= number_format($fees_total, 2); ?></div>
            </div>
            <?php if ($can_admin_edit): ?>
                <a class="btn btn-outline-primary" href="<?= site_url('students/enroll/' . (int) $student->id . '?' . http_build_query([
                    'edit' => '1',
                    'academic_year' => $student->academic_year,
                    'grade_code' => $student->grade_level,
                    'payment_mode' => $student->payment_mode
                ])); ?>"><i class="fas fa-edit me-1" aria-hidden="true"></i>Edit enrollment</a>
            <?php elseif ($is_admin && !empty($student->enrollment_id)): ?>
                <span class="small text-muted">Editing is locked after a payment is recorded.</span>
            <?php endif; ?>
        </div>
        <div class="card border-0 shadow-sm mb-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0 student-table">
                    <thead class="table-light"><tr><th>Category</th><th>Payment</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        <?php if (!empty($saved_fee_rows)): ?>
                            <?php foreach ($saved_fee_rows as $fee): ?>
                                <tr>
                                    <td><?= html_escape(ucwords(str_replace('_', ' ', $fee->fee_category))); ?></td>
                                    <td><?= html_escape($fee->payment_label); ?></td>
                                    <td><?= !empty($fee->payment_date) ? html_escape($fee->payment_date) : '—'; ?></td>
                                    <td class="text-end">₱<?= number_format((float) $fee->amount, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No fee items were saved for this enrollment.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert alert-success" role="status">This enrollment and its fee assessment have been saved.</div>
    <?php elseif ($placement_error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Enrollment placement is not valid.</strong> <?= html_escape($placement_error); ?>
        </div>
    <?php elseif ($configuration_checked && !$fees): ?>
        <div class="alert alert-warning" role="alert">
            No active fee configuration was found for <?= html_escape($selected['academic_year']); ?>, Grade <?= html_escape($selected['grade_code']); ?>, <?= html_escape($payment_modes[$selected['payment_mode']]); ?>.
        </div>
    <?php elseif ($fees): ?>
        <?php if ($admin_edit): ?>
            <div class="alert alert-info" role="status">Admin edit mode: saving updates this enrollment and refreshes its fee assessment. Editing is only allowed before a payment is recorded.</div>
        <?php endif; ?>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Configured charges</h2>
                <div class="text-muted small">
                    <?= html_escape($selected['academic_year']); ?> · Grade <?= html_escape($selected['grade_code']); ?>
                    
                    · <?= html_escape($payment_modes[$selected['payment_mode']]); ?>
                </div>
            </div>
            <div class="text-md-end">
                <div class="small text-muted"><?= $enrollment_saved ? 'Saved assessment total' : 'Estimated total'; ?><?= $selected_uniform ? ' including selected uniform' : ''; ?></div>
                <div class="h4 fw-bold mb-0">₱<?= number_format($fees_total, 2); ?></div>
            </div>
        </div>

        <?php foreach (['tuition' => ['Tuition', 'tuition_fee'], 'worktext' => ['Worktext', 'amount'], 'extra_curricular' => ['Extra-curricular', 'amount']] as $category => $definition): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><?= html_escape($definition[0]); ?></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 student-table">
                        <thead class="table-light"><tr><th>Payment</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            <?php if (!empty($fees[$category])): ?>
                                <?php foreach ($fees[$category] as $fee): ?>
                                    <tr>
                                        <td><?= html_escape($fee->payment_label); ?></td>
                                        <td><?= !empty($fee->payment_date) ? html_escape($fee->payment_date) : '—'; ?></td>
                                        <td class="text-end">₱<?= number_format((float) $fee->{$definition[1]}, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-muted text-center py-3">No active schedule items configured.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($selected_uniform): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Uniform · <?= html_escape(ucfirst($uniform_type)); ?></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 student-table">
                        <thead class="table-light"><tr><th>Size</th><th class="text-end">Top</th><th class="text-end">Bottom</th><th class="text-end">Total</th></tr></thead>
                        <tbody><tr>
                            <td><?= html_escape($selected_uniform->uniform_size); ?></td>
                            <td class="text-end">₱<?= number_format((float) $selected_uniform->top_price, 2); ?></td>
                            <td class="text-end">₱<?= number_format((float) $selected_uniform->bottom_price, 2); ?></td>
                            <td class="text-end fw-semibold">₱<?= number_format((float) $selected_uniform->top_price + (float) $selected_uniform->bottom_price, 2); ?></td>
                        </tr></tbody>
                    </table>
                </div>
            </div>
        <?php elseif (!empty($uniforms)): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Uniform pricing · <?= html_escape(ucfirst($uniform_type)); ?></div>
                <div class="card-body text-muted">Choose a size above to include its configured price in the estimate.</div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('students/enroll/' . (int) $student->id); ?>" class="card border-0 shadow-sm mt-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="fw-semibold"><?= $admin_edit ? 'Update enrollment' : 'Confirm ' . html_escape(strtolower($enrollment_type)); ?></div>
                    <div class="small text-muted"><?= $admin_edit ? 'The existing enrollment record will be updated.' : 'The selected fee schedule will be saved with this enrollment.'; ?></div>
                </div>
                <div>
                    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                    <input type="hidden" name="academic_year" value="<?= html_escape($selected['academic_year']); ?>">
                    <input type="hidden" name="grade_code" value="<?= html_escape($selected['grade_code']); ?>">
                    <input type="hidden" name="payment_mode" value="<?= html_escape($selected['payment_mode']); ?>">
                    <input type="hidden" name="uniform_size" value="<?= html_escape($selected['uniform_size']); ?>">
                    <?php if ($initial_flow): ?><input type="hidden" name="flow" value="initial"><?php endif; ?>
                    <?php if ($admin_edit): ?>
                        <input type="hidden" name="edit_enrollment" value="1">
                        <input type="hidden" name="enrollment_id" value="<?= (int) $student->enrollment_id; ?>">
                    <?php endif; ?>
                    <button class="btn <?= $admin_edit ? 'btn-primary' : 'btn-success'; ?>" type="submit"><?= $admin_edit ? 'Update enrollment' : 'Confirm enrollment'; ?></button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="<?= base_url('assets/js/student/enrollment.js'); ?>"></script>
