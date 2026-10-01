<?php
$teacherName = trim(implode(' ', array_filter([
    $teacher->first_name,
    $teacher->middle_name,
    $teacher->last_name,
])));
?>

<?php if (!empty($success_message)): ?><div class="alert alert-success alert-dismissible fade show" role="status"><?= html_escape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= html_escape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-1"><?= html_escape($teacherName); ?> — Payroll</h2>
            <div class="small text-secondary">Employee no. <?= html_escape($teacher->employee_no); ?> · Payroll periods, deductions, and payment history</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('payroll'); ?>" class="btn btn-primary btn-sm">View payroll runs</a>
            <a href="<?= site_url('teachers/salary/' . (int) $teacher->id); ?>" class="btn btn-outline-secondary btn-sm">Salary history</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Salary period</th>
                    <th>Pay date</th>
                    <th>Schedule</th>
                    <th class="text-end">Salary</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end">Take-home pay</th>
                    <th>Payment status</th>
                    <th class="text-end pe-3">Payslip</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payroll_items)): ?>
                    <tr><td colspan="8" class="py-5 text-center text-secondary">No payroll entries for this employee yet. Payroll entries are created when a payroll run is prepared and finalized.</td></tr>
                <?php else: ?>
                    <?php foreach ($payroll_items as $item): ?>
                        <?php $paymentLabel = $item->period_status !== 'finalized' ? 'Draft' : (($item->payment_status ?? 'unpaid') === 'paid' ? 'Paid' : 'Finalized - awaiting payment'); $paymentBadge = $item->period_status !== 'finalized' ? 'warning' : (($item->payment_status ?? 'unpaid') === 'paid' ? 'success' : 'info'); ?>
                        <tr>
                            <td class="ps-3 fw-semibold"><?= html_escape($item->period_start . ' to ' . $item->period_end); ?>
                                <div class="small text-secondary"><?= html_escape($item->payable_days ?? '—'); ?> days attended · <?= html_escape($item->paid_leave_days); ?> paid leave · <?= html_escape($item->absence_days); ?> unpaid absence</div>
                            </td>
                            <td><?= html_escape($item->pay_date); ?></td>
                            <td><?= $item->frequency === 'weekly' ? 'Weekly' : ($item->frequency === 'semi_monthly' ? '15th and month-end' : 'Legacy monthly'); ?></td>
                            <td class="text-end">₱<?= number_format((float) $item->basic_pay, 2); ?></td>
                            <td class="text-end">₱<?= number_format((float) $item->total_deductions, 2); ?></td>
                            <td class="text-end fw-semibold">₱<?= number_format((float) $item->net_pay, 2); ?></td>
                            <td><span class="badge text-bg-<?= $paymentBadge; ?>"><?= html_escape($paymentLabel); ?></span></td>
                            <td class="text-end pe-3">
                                <?php if ($item->period_status === 'finalized'): ?>
                                    <div class="d-flex flex-wrap justify-content-end gap-1">
                                        <a href="<?= site_url('payroll/payslip/' . (int) $item->id); ?>" class="btn btn-sm btn-outline-primary">Download</a>
                                        <form method="post" action="<?= site_url('payroll/payslip/' . (int) $item->id . '/email'); ?>">
                                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-envelope me-1" aria-hidden="true"></i>Email</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="small text-secondary">After finalization</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        <a href="<?= site_url('payroll'); ?>" class="btn btn-sm btn-outline-secondary">Back to payroll</a>
    </div>
</section>
