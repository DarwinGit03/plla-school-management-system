<div class="container-fluid finance-configurations">
    <?php foreach (['success', 'error'] as $flash_type): ?>
        <?php if ($this->session->flashdata($flash_type)): ?>
            <div class="alert alert-<?= $flash_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?= html_escape($this->session->flashdata($flash_type)); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Configuration records</h2>
                <p class="text-muted small mb-0">Fee schedules configured for each school year, grade, and payment mode.</p>
            </div>
            <form method="get" action="<?= site_url('finance'); ?>" class="d-flex gap-2 align-items-center">
                <label for="configuration-school-year" class="small text-muted mb-0">School year</label>
                <select id="configuration-school-year" name="school_year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All years</option>
                    <?php foreach ($school_years as $year): ?>
                        <option value="<?= html_escape($year->school_year); ?>" <?= $selected_school_year === $year->school_year ? 'selected' : ''; ?>><?= html_escape($year->school_year); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="badge text-bg-secondary text-nowrap"><?= number_format(count($configurations)); ?> records</span>
            </form>
        </div>

        <?php
        $whole_year_targets = array_keys($year_inventory);
        if (!empty($whole_year_targets)) {
            $latest_year = $whole_year_targets[0];
            if (preg_match('/^(\d{4})-(\d{4})$/', $latest_year, $parts)) {
                $whole_year_targets[] = ((int) $parts[1] + 1) . '-' . ((int) $parts[2] + 1);
            }
        }
        $whole_year_targets = array_values(array_unique($whole_year_targets));
        rsort($whole_year_targets, SORT_STRING);
        ?>
        <div class="card-body border-top d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h3 class="h6 fw-bold mb-1">Copy the whole school year</h3>
                <p class="small text-muted mb-0">Copy all grades, payment modes, and fee schedules into another school year.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#duplicateSchoolYearModal">
                    <i class="fas fa-copy me-1" aria-hidden="true"></i>Duplicate whole year
                </button>
                <?php if ($selected_school_year !== ''): ?>
                    <button type="button" class="btn btn-outline-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#updateSchoolYearStatusModal">
                        <i class="fas fa-toggle-on me-1" aria-hidden="true"></i>Update year status
                    </button>
                    <?php if ($selected_year_deletable): ?>
                        <button type="button" class="btn btn-outline-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#deleteSchoolYearModal">
                            <i class="fas fa-trash-alt me-1" aria-hidden="true"></i>Delete unused draft year
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal fade" id="duplicateSchoolYearModal" tabindex="-1" aria-labelledby="duplicateSchoolYearModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="<?= site_url('finance/configuration/duplicate-year'); ?>">
                        <div class="modal-header">
                            <h2 class="modal-title h5 fw-bold" id="duplicateSchoolYearModalLabel">Duplicate a school year</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                            <div class="mb-3">
                                <label for="duplicate-source-year" class="form-label">Copy fees from</label>
                                <select id="duplicate-source-year" name="source_school_year" class="form-select" required>
                                    <option value="">Choose source school year</option>
                                    <?php foreach ($school_years as $year): ?>
                                        <option value="<?= html_escape($year->school_year); ?>" <?= $selected_school_year === $year->school_year ? 'selected' : ''; ?>><?= html_escape($year->school_year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="duplicate-target-year" class="form-label">Copy fees to</label>
                                <select id="duplicate-target-year" name="target_school_year" class="form-select" required>
                                    <option value="">Choose destination school year</option>
                                    <?php foreach ($whole_year_targets as $year): ?>
                                        <?php $existing_fee_data = $year_inventory[$year] ?? null; ?>
                                        <option value="<?= html_escape($year); ?>" <?= $existing_fee_data ? 'disabled' : ''; ?>><?= html_escape($year); ?><?= $existing_fee_data ? ' (already has fee data)' : ''; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="duplicate-configuration-status" class="form-label">Status of copied configurations</label>
                                <select id="duplicate-configuration-status" name="configuration_status" class="form-select" required>
                                    <option value="draft" selected>Draft - review before use</option>
                                    <option value="active">Active - available for enrollment</option>
                                    <option value="inactive">Inactive - unavailable for enrollment</option>
                                </select>
                            </div>
                            <div class="form-text mt-3">Years already containing fee schedules are disabled. The next empty year is available automatically.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-copy me-1" aria-hidden="true"></i>Duplicate year</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($selected_school_year !== ''): ?>
            <div class="modal fade" id="updateSchoolYearStatusModal" tabindex="-1" aria-labelledby="updateSchoolYearStatusModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="<?= site_url('finance/configuration/update-year-status'); ?>">
                            <div class="modal-header">
                                <h2 class="modal-title h5 fw-bold" id="updateSchoolYearStatusModalLabel">Update status for <?= html_escape($selected_school_year); ?></h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                <input type="hidden" name="school_year" value="<?= html_escape($selected_school_year); ?>">
                                <label for="year-wide-status" class="form-label">New status for all <?= number_format(count($configurations)); ?> configurations</label>
                                <select id="year-wide-status" name="status" class="form-select" required>
                                    <option value="" <?= $selected_year_status === '' ? 'selected' : ''; ?>>Choose status</option>
                                    <?php foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $status_value => $status_label): ?>
                                        <option value="<?= $status_value; ?>" <?= $selected_year_status === $status_value ? 'selected' : ''; ?>><?= $status_label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text mt-2">Setting a year to Active makes its fee configurations available during enrollment.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update all statuses</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selected_school_year !== '' && $selected_year_deletable): ?>
            <div class="modal fade" id="deleteSchoolYearModal" tabindex="-1" aria-labelledby="deleteSchoolYearModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="<?= site_url('finance/configuration/delete-year'); ?>">
                            <div class="modal-header">
                                <h2 class="modal-title h5 fw-bold" id="deleteSchoolYearModalLabel">Delete school year <?= html_escape($selected_school_year); ?>?</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                <input type="hidden" name="school_year" value="<?= html_escape($selected_school_year); ?>">
                                <p>This removes the year’s fee configurations and schedules. Deletion is allowed only when all configurations are drafts and no student enrollment uses the year.</p>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm-delete-school-year" name="confirm_delete" value="1" required>
                                    <label class="form-check-label" for="confirm-delete-school-year">I understand this will permanently delete this unused draft year.</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1" aria-hidden="true"></i>Delete draft year</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="px-3">#</th>
                        <th scope="col">School Year</th>
                        <th scope="col">Grade</th>
                        <th scope="col">Payment Mode</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col" class="text-end px-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($configurations)): ?>
                        <?php foreach ($configurations as $index => $configuration): ?>
                            <?php
                            $payment_modes = [
                                'tri_term' => 'Tri-Term',
                                'semi_annual' => 'Semi-Annual',
                                'annual' => 'Annual'
                            ];
                            $status = strtolower((string) $configuration->status);
                            $status_classes = [
                                'active' => 'success',
                                'draft' => 'warning',
                                'inactive' => 'secondary'
                            ];
                            $status_class = $status_classes[$status] ?? 'light';
                            $grade = (string) $configuration->grade_code;
                            $grade_label = $grade === 'N'
                                ? 'Nursery'
                                : ($grade === 'K' ? 'Kindergarten' : 'Grade ' . $grade);
                            ?>
                            <tr>
                                <td class="px-3 text-muted"><?= (int) $index + 1; ?></td>
                                <td class="fw-semibold"><?= html_escape($configuration->school_year); ?></td>
                                <td><?= html_escape($grade_label); ?></td>
                                <td><?= html_escape($payment_modes[$configuration->payment_mode] ?? $configuration->payment_mode); ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $status_class; ?>">
                                        <?= html_escape(ucfirst($status)); ?>
                                    </span>
                                </td>
                                <td><?= html_escape($configuration->created_at ?: '—'); ?></td>
                                <td class="text-end px-3">
                                    <a
                                        href="<?= site_url('finance/view/' . (int) $configuration->id); ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1" aria-hidden="true"></i>
                                        View
                                    </a>
                                    <a href="<?= site_url('finance/configuration/edit/' . (int) $configuration->id); ?>" class="btn btn-sm btn-outline-secondary ms-1">
                                        <i class="fas fa-edit me-1" aria-hidden="true"></i>Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No fee configurations found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
