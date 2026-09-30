<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= html_escape($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= html_escape($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <form method="get" action="<?= site_url('teachers'); ?>" class="row g-2 align-items-end flex-grow-1">
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="teacherSearch" class="form-label small fw-semibold">Search faculty</label>
                    <input type="search" id="teacherSearch" name="q" class="form-control" value="<?= html_escape($search); ?>" placeholder="Employee no., name, email, or position">
                </div>
                <div class="col-6 col-md-3">
                    <label for="teacherStatus" class="form-label small fw-semibold">Status</label>
                    <select id="teacherStatus" name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" <?= $selected_status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?= $selected_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-filter me-1" aria-hidden="true"></i>Filter</button>
                    <a href="<?= site_url('teachers'); ?>" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
            <?php if (!empty($can_manage_profiles)): ?>
                <a href="<?= site_url('teachers/create'); ?>" class="btn btn-primary flex-shrink-0"><i class="fas fa-plus me-1" aria-hidden="true"></i>Register teacher</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-bold mb-0">Faculty members <span class="text-secondary fw-normal">· <?= count($teachers); ?></span></h2>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Employee no.</th>
                    <th>Teacher</th>
                    <th>Position</th>
                    <th>Employment</th>
                    <th class="text-end">Current salary</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="7" class="py-5 text-center text-secondary">No teacher profiles found. Register a teacher to get started.</td></tr>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <?php $name = trim(implode(' ', array_filter([$teacher->first_name, $teacher->middle_name, $teacher->last_name, $teacher->suffix]))); ?>
                        <tr>
                            <td class="ps-3 fw-semibold"><?= html_escape($teacher->employee_no); ?></td>
                            <td>
                                <div class="fw-semibold"><?= html_escape($name); ?></div>
                                <?php if (!empty($teacher->work_email)): ?><div class="small text-secondary"><?= html_escape($teacher->work_email); ?></div><?php endif; ?>
                            </td>
                            <td><?= html_escape($teacher->position_title ?: '—'); ?></td>
                            <td><?= html_escape(ucwords(str_replace('_', ' ', $teacher->employment_type))); ?></td>
                            <td class="text-end">
                                <?php if (!empty($teacher->salary_type)): ?>
                                    <?php $currentRate = $teacher->salary_type === 'daily' ? $teacher->daily_salary : $teacher->monthly_salary; ?>
                                    <span class="fw-semibold">₱<?= number_format((float) $currentRate, 2); ?></span>
                                    <span class="small text-secondary">/ <?= $teacher->salary_type === 'daily' ? 'day' : 'month'; ?></span>
                                    <div class="small text-secondary"><?= $teacher->pay_frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></div>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Not set</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge text-bg-<?= $teacher->status === 'active' ? 'success' : 'secondary'; ?>"><?= html_escape(ucfirst($teacher->status)); ?></span></td>
                            <td class="text-end pe-3">
                                <a href="<?= site_url('payroll/teacher/' . (int) $teacher->id); ?>" class="btn btn-sm btn-outline-secondary" title="Pay history"><i class="fas fa-history me-1" aria-hidden="true"></i>History</a>
                                <a href="<?= site_url('teachers/salary/' . (int) $teacher->id); ?>#salary-change" class="btn btn-sm btn-outline-success"><i class="fas fa-arrow-trend-up me-1" aria-hidden="true"></i><?= !empty($teacher->salary_type) ? 'Promote / raise' : 'Set salary'; ?></a>
                                <?php if (!empty($can_manage_profiles)): ?>
                                    <a href="<?= site_url('teachers/edit/' . (int) $teacher->id); ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen me-1" aria-hidden="true"></i>Edit</a>
                                <form method="post" action="<?= site_url('teachers/change-status'); ?>" class="d-inline">
                                    <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                                    <input type="hidden" name="id" value="<?= (int) $teacher->id; ?>">
                                    <input type="hidden" name="status" value="<?= $teacher->status === 'active' ? 'inactive' : 'active'; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('<?= $teacher->status === 'active' ? 'Deactivate' : 'Activate'; ?> this teacher profile?');">
                                        <?= $teacher->status === 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
