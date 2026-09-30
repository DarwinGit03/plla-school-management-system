<?php if (!$teacher): ?>
    <div class="alert alert-info" role="status">
        Your login is not linked to a teacher profile yet. Please contact the school administrator to link your account.
    </div>
<?php else: ?>
    <?php $teacherName = trim(implode(' ', array_filter([$teacher->first_name, $teacher->middle_name, $teacher->last_name, $teacher->suffix]))); ?>
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>
                <h2 class="h5 fw-bold mb-1"><?= html_escape($teacherName); ?></h2>
                <div class="text-secondary">Employee no. <?= html_escape($teacher->employee_no); ?></div>
            </div>
            <span class="badge text-bg-light border align-self-start">Private account view</span>
        </div>
    </section>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Salary rate history</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Effective from</th><th>Effective to</th><th>Salary basis</th><th>Pay schedule</th><th>Reason</th><th class="text-end pe-3">Rate</th></tr></thead>
                <tbody>
                    <?php if (!$salary_history): ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">No salary rate history is available.</td></tr>
                    <?php else: foreach ($salary_history as $rate): ?>
                        <?php $frequency = $rate->pay_frequency ?? 'semi_monthly'; $amount = $rate->salary_type === 'daily' ? $rate->daily_salary : $rate->monthly_salary; ?>
                        <tr>
                            <td class="ps-3"><?= html_escape($rate->effective_from); ?></td>
                            <td><?= empty($rate->effective_to) ? '<span class="badge text-bg-success">Current</span>' : html_escape($rate->effective_to); ?></td>
                            <td><?= $rate->salary_type === 'daily' ? 'Daily' : 'Monthly'; ?></td>
                            <td><?= $frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></td>
                            <td><?= html_escape($rate->change_reason ?? '—'); ?></td>
                            <td class="text-end pe-3">PHP <?= number_format((float) $amount, 2); ?><?= $rate->salary_type === 'daily' ? ' / day' : ' / month'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Payroll and payslips</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Pay period</th><th>Pay date</th><th>Schedule</th><th class="text-end">Gross pay</th><th class="text-end">Deductions</th><th class="text-end">Net pay</th><th>Status</th><th class="text-end pe-3">Payslip</th></tr></thead>
                <tbody>
                    <?php if (!$payroll_items): ?>
                        <tr><td colspan="8" class="text-center text-secondary py-4">No payroll records are available yet.</td></tr>
                    <?php else: foreach ($payroll_items as $item): $paymentLabel = $item->payment_status === 'paid' ? 'Paid' : 'Finalized - awaiting payment'; $paymentBadge = $item->payment_status === 'paid' ? 'success' : 'warning'; ?>
                        <tr>
                            <td class="ps-3 fw-semibold"><?= html_escape($item->period_start . ' to ' . $item->period_end); ?><div class="small text-secondary"><?= html_escape($item->payable_days ?? '—'); ?> attended · <?= html_escape($item->paid_leave_days); ?> paid leave · <?= html_escape($item->absence_days); ?> unpaid absent</div></td>
                            <td><?= html_escape($item->pay_date); ?></td>
                            <td><?= $item->frequency === 'weekly' ? 'Weekly' : ($item->frequency === 'semi_monthly' ? '15th and month-end' : 'Legacy monthly'); ?></td>
                            <td class="text-end">PHP <?= number_format((float) $item->basic_pay, 2); ?></td>
                            <td class="text-end">PHP <?= number_format((float) $item->total_deductions, 2); ?></td>
                            <td class="text-end fw-semibold">PHP <?= number_format((float) $item->net_pay, 2); ?></td>
                            <td><span class="badge text-bg-<?= $paymentBadge; ?>"><?= html_escape($paymentLabel); ?></span></td>
                            <td class="text-end pe-3"><?php if ($item->period_status === 'finalized'): ?><a class="btn btn-sm btn-outline-primary" href="<?= site_url('my-payroll/payslip/' . (int) $item->id); ?>">Download</a><?php else: ?><span class="small text-secondary">Not finalized</span><?php endif; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
