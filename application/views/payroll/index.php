<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$payrollForm = is_array($payroll_form ?? null) ? $payroll_form : [];
$frequencyValue = $payrollForm['frequency'] ?? 'semi_monthly';
?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= html_escape($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<section class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-1">Payroll runs <span class="text-secondary fw-normal">(<?= count($periods); ?>)</span></h2>
            <p class="small text-secondary mb-0">Each run contains every employee paid on that schedule. Download the period summary or open a run to review and pay employees.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (has_permission('payroll.settings.manage')): ?><a class="btn btn-outline-secondary" href="<?= site_url('payroll/settings'); ?>">Deduction settings</a><?php endif; ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#preparePayrollModal">
                <i class="fas fa-plus me-1" aria-hidden="true"></i>Prepare payroll run
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Salary period</th>
                    <th>Pay date</th>
                    <th>Schedule</th>
                    <th>Employees</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($periods)): ?>
                    <tr><td colspan="6" class="py-5 text-center text-secondary">No payroll runs yet. Select Prepare payroll run to begin.</td></tr>
                <?php else: foreach ($periods as $period): ?>
                    <tr>
                        <td class="ps-3 fw-semibold"><?= html_escape($period->period_start . ' to ' . $period->period_end); ?></td>
                        <td><?= html_escape($period->pay_date); ?></td>
                        <td><?= $period->frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></td>
                        <td><?= (int) $period->employee_count; ?></td>
                        <td><span class="badge text-bg-<?= $period->status === 'finalized' ? 'success' : 'warning'; ?>"><?= html_escape(ucfirst($period->status)); ?></span></td>
                        <td class="text-end pe-3 text-nowrap">
                            <a href="<?= site_url('payroll/period/' . (int) $period->id); ?>" class="btn btn-sm btn-outline-primary"><?= $period->status === 'finalized' ? 'View run' : 'Continue'; ?></a>
                            <a href="<?= site_url('payroll/summary/' . (int) $period->id); ?>" class="btn btn-sm btn-outline-secondary">Download summary</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash !== '#preparePayrollModal' || !window.bootstrap) return;
    var modalElement = document.getElementById('preparePayrollModal');
    if (modalElement) bootstrap.Modal.getOrCreateInstance(modalElement).show();
});
</script>

<div class="modal fade" id="preparePayrollModal" tabindex="-1" aria-labelledby="preparePayrollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('payroll/create-period'); ?>">
                <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5 fw-bold" id="preparePayrollModalLabel">Prepare a payroll run</h2>
                        <p class="small text-secondary mb-0">Choose the pay schedule and dates for this payroll.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (trim((string) $error_message) !== ''): ?>
                        <div class="alert alert-danger mb-3" role="alert">
                            <?= html_escape($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="frequency" class="form-label">Pay schedule</label>
                            <select id="frequency" name="frequency" class="form-select" required>
                                <option value="semi_monthly" <?= $frequencyValue === 'semi_monthly' ? 'selected' : ''; ?>>15th and month-end</option>
                                <option value="weekly" <?= $frequencyValue === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="pay_date" class="form-label">Pay date</label>
                            <input type="date" id="pay_date" name="pay_date" class="form-control" value="<?= html_escape($payrollForm['pay_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="period_start" class="form-label">Salary period from</label>
                            <input type="date" id="period_start" name="period_start" class="form-control" value="<?= html_escape($payrollForm['period_start'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="period_end" class="form-label">Salary period to</label>
                            <input type="date" id="period_end" name="period_end" class="form-control" value="<?= html_escape($payrollForm['period_end'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-text mt-3">The run includes every active employee with a salary rate covering this period. Next, confirm each employee’s attendance and absences, then enter any other deductions.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Continue to payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-1">Paid history <span class="text-secondary fw-normal">(<?= count($paid_history); ?>)</span></h2>
            <p class="small text-secondary mb-0">All recorded employee payments.</p>
        </div>
        <form method="get" action="<?= site_url('payroll'); ?>" class="d-flex gap-2">
            <label class="visually-hidden" for="paidHistorySearch">Find paid employee</label>
            <input type="search" id="paidHistorySearch" name="q" class="form-control form-control-sm" value="<?= html_escape($search); ?>" placeholder="Employee name or number">
            <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
            <?php if ($search !== ''): ?><a href="<?= site_url('payroll'); ?>" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-3">Employee</th><th>Pay period</th><th>Pay date</th><th>Schedule</th><th>Payment details</th><th class="text-end">Gross pay</th><th class="text-end">Deductions</th><th class="text-end">Net paid</th><th class="text-end pe-3">Payslip</th></tr>
            </thead>
            <tbody>
                <?php if (empty($paid_history)): ?>
                    <tr><td colspan="9" class="py-5 text-center text-secondary"><?= $search !== '' ? 'No paid records match that employee.' : 'No employee payments have been recorded yet.'; ?></td></tr>
                <?php else: foreach ($paid_history as $paid): ?>
                    <tr>
                        <td class="ps-3"><div class="fw-semibold"><?= html_escape($paid->employee_name_snapshot); ?></div><div class="small text-secondary"><?= html_escape($paid->employee_no_snapshot); ?></div></td>
                        <td><?= html_escape($paid->period_start . ' to ' . $paid->period_end); ?><div class="small text-secondary"><?= html_escape($paid->payable_days); ?> attended · <?= html_escape($paid->paid_leave_days); ?> paid leave · <?= html_escape($paid->absence_days); ?> unpaid absent</div></td>
                        <td><?= html_escape($paid->pay_date); ?></td>
                        <td><?= $paid->frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></td>
                        <td><?= html_escape(ucwords(str_replace('_', ' ', $paid->payment_method))); ?><div class="small text-secondary"><?= html_escape($paid->paid_at); ?></div><?php if (!empty($paid->payment_reference)): ?><div class="small text-secondary">Ref: <?= html_escape($paid->payment_reference); ?></div><?php endif; ?></td>
                        <td class="text-end">PHP <?= number_format((float) $paid->basic_pay, 2); ?></td>
                        <td class="text-end">PHP <?= number_format((float) $paid->total_deductions, 2); ?></td>
                        <td class="text-end fw-semibold">PHP <?= number_format((float) $paid->net_pay, 2); ?></td>
                        <td class="text-end pe-3">
                            <div class="d-flex flex-wrap justify-content-end gap-1">
                                <a href="<?= site_url('payroll/payslip/' . (int) $paid->payroll_item_id); ?>" class="btn btn-sm btn-outline-primary">Payslip</a>
                                <form method="post" action="<?= site_url('payroll/payslip/' . (int) $paid->payroll_item_id . '/email'); ?>">
                                    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Email payslip"><i class="fas fa-envelope" aria-hidden="true"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
