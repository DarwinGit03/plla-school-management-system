<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$periodGross = 0.0;
$periodDeductions = 0.0;
foreach ($items as $item) {
    $periodGross += (float) $item->basic_pay;
    $periodDeductions += (float) $item->absence_deduction;
    $tax = $tax_deductions[$item->id] ?? null;
    $periodDeductions += (float) ($tax->sss_amount ?? 0) + (float) ($tax->philhealth_amount ?? 0) + (float) ($tax->pagibig_amount ?? 0) + (float) ($tax->withholding_tax_amount ?? 0);
    foreach ($deductions[$item->id] ?? [] as $deduction) {
        $periodDeductions += (float) $deduction->amount;
    }
}
?>

<?php if (!empty($success_message)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert"><?= html_escape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert"><?= html_escape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="small text-secondary">Payroll coverage · <?= $period->frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></div>
            <h2 class="h5 fw-bold mb-1"><?= html_escape($period->period_start); ?> to <?= html_escape($period->period_end); ?></h2>
            <div class="small text-secondary">Payment date: <?= html_escape($period->pay_date); ?></div>
        </div>
        <div class="d-flex flex-wrap gap-4">
            <div><div class="small text-secondary">Gross pay</div><strong>PHP <?= number_format($periodGross, 2); ?></strong></div>
            <div><div class="small text-secondary">Deductions</div><strong class="text-danger">PHP <?= number_format($periodDeductions, 2); ?></strong></div>
            <div><div class="small text-secondary">Net pay</div><strong class="text-success">PHP <?= number_format($periodGross - $periodDeductions, 2); ?></strong></div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 py-3">
        <div>
            <h2 class="h6 fw-bold mb-1">Employees in this payroll</h2>
            <p class="small text-secondary mb-2"><span id="payrollEmployeeCount"><?= count($items); ?></span> employees included. Review each employee's pay, attendance, and deductions before finalizing.</p>
            <p class="small text-secondary mb-2">Statutory contributions and withholding tax are calculated automatically from payroll settings.</p>
            <label class="visually-hidden" for="payrollEmployeeSearch">Search employees</label>
            <input type="search" id="payrollEmployeeSearch" class="form-control form-control-sm w-auto" placeholder="Search name or employee number">
        </div>
        <?php if ($period->status === 'draft'): ?>
            <form method="post" action="<?= site_url('payroll/finalize'); ?>" onsubmit="return confirm('Finalize this payroll? Finalized payroll and payslips cannot be edited.');">
                <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                <input type="hidden" name="period_id" value="<?= (int) $period->id; ?>">
                <button type="submit" class="btn btn-success"><i class="fas fa-lock me-1" aria-hidden="true"></i>Finalize payroll</button>
            </form>
        <?php else: ?>
            <span class="badge text-bg-success fs-6">Finalized</span>
        <?php endif; ?>
    </div>
    <div class="p-3 p-lg-4">
        <?php if (empty($items)): ?>
            <div class="text-center text-secondary py-5">No employees were included in this payroll.</div>
        <?php else: ?>
            <div class="row g-3">
            <?php foreach ($items as $item): ?>
                <?php
                $itemDeductions = $deductions[$item->id] ?? [];
                $manualDeductions = array_sum(array_map(function ($row) { return (float) $row->amount; }, $itemDeductions));
                $itemTaxes = $tax_deductions[$item->id] ?? null;
                $taxTotal = (float) ($itemTaxes->sss_amount ?? 0) + (float) ($itemTaxes->philhealth_amount ?? 0) + (float) ($itemTaxes->pagibig_amount ?? 0) + (float) ($itemTaxes->withholding_tax_amount ?? 0);
                $deductionTotal = $manualDeductions + (float) $item->absence_deduction + $taxTotal;
                $searchText = strtolower($item->employee_name_snapshot . ' ' . $item->employee_no_snapshot);
                $periodDays = (int) (new DateTime($period->period_start))->diff(new DateTime($period->period_end))->days + 1;
                ?>
                <div class="col-12" id="payroll-item-<?= (int) $item->id; ?>" data-payroll-employee="<?= html_escape($searchText); ?>">
                    <article class="card payroll-employee-card h-100">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                                <div>
                                    <h3 class="h6 fw-bold mb-1"><?= html_escape($item->employee_name_snapshot); ?></h3>
                                    <div class="small text-secondary">Employee no. <?= html_escape($item->employee_no_snapshot); ?><?php if ($item->position_snapshot): ?> &middot; <?= html_escape($item->position_snapshot); ?><?php endif; ?></div>
                                    <div class="small text-secondary mt-1">Rate: PHP <?= number_format((float) ($item->salary_type_snapshot === 'daily' ? $item->daily_salary_snapshot : $item->monthly_salary_snapshot), 2); ?> / <?= $item->salary_type_snapshot === 'daily' ? 'day' : 'month'; ?></div>
                                </div>
                                <?php if ($period->status === 'draft'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary align-self-lg-start edit-payroll-button"
                                        data-bs-toggle="modal" data-bs-target="#employeePayrollModal"
                                        data-item-id="<?= (int) $item->id; ?>"
                                        data-employee-name="<?= html_escape($item->employee_name_snapshot, true); ?>"
                                        data-employee-no="<?= html_escape($item->employee_no_snapshot, true); ?>"
                                        data-attended="<?= html_escape($item->payable_days ?? ''); ?>"
                                        data-paid-leave="<?= html_escape($item->paid_leave_days); ?>"
                                        data-unpaid-absence="<?= html_escape($item->absence_days); ?>"
                                        data-period-days="<?= $periodDays; ?>"
                                        data-absence-deduction="<?= number_format((float) $item->absence_deduction, 2, '.', ''); ?>"
                                        data-sss="<?= number_format((float) ($itemTaxes->sss_amount ?? 0), 2, '.', ''); ?>"
                                        data-philhealth="<?= number_format((float) ($itemTaxes->philhealth_amount ?? 0), 2, '.', ''); ?>"
                                        data-pagibig="<?= number_format((float) ($itemTaxes->pagibig_amount ?? 0), 2, '.', ''); ?>"
                                        data-withholding="<?= number_format((float) ($itemTaxes->withholding_tax_amount ?? 0), 2, '.', ''); ?>"
                                        data-deductions="<?= html_escape(json_encode(array_map(function ($row) { return ['category' => $row->category, 'description' => $row->description, 'amount' => $row->amount]; }, $itemDeductions))); ?>">
                                        <i class="fas fa-pen me-1" aria-hidden="true"></i>Edit attendance &amp; deductions
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6 col-xl-3"><div class="payroll-summary-box"><div class="small text-secondary">Attendance</div><strong><?= html_escape($item->payable_days ?? 'Not entered'); ?></strong> days attended<div class="small text-secondary mt-1"><?= html_escape($item->paid_leave_days); ?> paid leave &middot; <?= html_escape($item->absence_days); ?> unpaid absent</div></div></div>
                                <div class="col-12 col-md-6 col-xl-3"><div class="payroll-summary-box"><div class="small text-secondary">Gross pay</div><strong>PHP <?= number_format((float) $item->basic_pay, 2); ?></strong><div class="small text-secondary mt-1">Before deductions</div></div></div>
                                <div class="col-12 col-md-6 col-xl-3"><div class="payroll-summary-box"><div class="small text-secondary">Total deductions</div><strong class="text-danger">PHP <?= number_format($deductionTotal, 2); ?></strong>
                                    <?php if ((float) $item->absence_deduction > 0): ?><div class="small">Unpaid absence: PHP <?= number_format((float) $item->absence_deduction, 2); ?></div><?php endif; ?>
                                    <?php if ($taxTotal > 0): ?><details class="small mt-1"><summary class="text-secondary">Taxes &amp; contributions (PHP <?= number_format($taxTotal, 2); ?>)</summary><div class="mt-1">SSS: PHP <?= number_format((float) ($itemTaxes->sss_amount ?? 0), 2); ?></div><div>PhilHealth: PHP <?= number_format((float) ($itemTaxes->philhealth_amount ?? 0), 2); ?></div><div>Pag-IBIG: PHP <?= number_format((float) ($itemTaxes->pagibig_amount ?? 0), 2); ?></div><div>Withholding tax: PHP <?= number_format((float) ($itemTaxes->withholding_tax_amount ?? 0), 2); ?></div></details><?php endif; ?>
                                    <?php if ($itemDeductions): ?><details class="small mt-1"><summary class="text-primary">Other deductions (<?= count($itemDeductions); ?>)</summary><ul class="list-unstyled mb-0 mt-2">
                                        <?php foreach ($itemDeductions as $deduction): ?><li class="d-flex justify-content-between align-items-start gap-2 py-1"><span><span class="badge text-bg-light"><?= html_escape(ucwords(str_replace('_', ' ', $deduction->category))); ?></span> <?= html_escape($deduction->description); ?><br>PHP <?= number_format((float) $deduction->amount, 2); ?></span>
                                        <?php if ($period->status === 'draft'): ?><form method="post" action="<?= site_url('payroll/deductions/delete'); ?>"><input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>"><input type="hidden" name="id" value="<?= (int) $deduction->id; ?>"><button class="btn btn-sm btn-link text-danger p-0" type="submit">Remove</button></form><?php endif; ?></li><?php endforeach; ?>
                                    </ul></details><?php endif; ?>
                                </div></div>
                                <div class="col-12 col-md-6 col-xl-3"><div class="payroll-net-box"><div class="small">Take-home pay</div><strong>PHP <?= number_format((float) $item->basic_pay - $deductionTotal, 2); ?></strong></div></div>
                            </div>
                            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 border-top mt-3 pt-3">
                                <div><div class="small text-secondary mb-1">Payment</div>
                                <?php if ($period->status !== 'finalized'): ?><span class="small text-secondary">Available after payroll is finalized</span>
                                <?php elseif ($item->payment_status === 'paid'): ?><span class="badge text-bg-success">Paid</span> <span class="small text-secondary"><?= html_escape(ucwords(str_replace('_', ' ', $item->payment_method))); ?></span>
                                <?php else: ?>
                                    <form method="post" action="<?= site_url('payroll/mark-paid'); ?>" class="d-flex flex-column flex-sm-row gap-2">
                                        <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>"><input type="hidden" name="payroll_item_id" value="<?= (int) $item->id; ?>">
                                        <select name="payment_method" class="form-select form-select-sm" required aria-label="Payment method"><option value="cash">Cash</option><option value="bank_transfer">Bank transfer</option><option value="check">Check</option><option value="other">Other</option></select>
                                        <input name="payment_reference" class="form-control form-control-sm" maxlength="100" placeholder="Reference (optional)" aria-label="Payment reference">
                                        <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Mark as paid</button>
                                    </form>
                                <?php endif; ?></div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($period->status === 'finalized'): ?>
                                        <a href="<?= site_url('payroll/payslip/' . (int) $item->id); ?>" class="btn btn-sm btn-outline-primary">Download payslip</a>
                                        <form method="post" action="<?= site_url('payroll/payslip/' . (int) $item->id . '/email'); ?>">
                                            <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-envelope me-1" aria-hidden="true"></i>Email employee</button>
                                        </form>
                                    <?php else: ?><span class="small text-secondary">Payslip available after finalization</span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white small text-secondary">Monthly salary covers scheduled days off. Unpaid absences are deducted using monthly salary / 26. Daily-paid salary includes attended days and paid leave; unpaid absences are not paid.</div>
</section>
<?php if ($period->status === 'draft'): ?>
<div class="modal fade" id="employeePayrollModal" tabindex="-1" aria-labelledby="employeePayrollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5 fw-bold" id="employeePayrollModalLabel">Update employee payroll</h2>
                    <div class="small text-secondary" id="employeePayrollIdentity"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <section class="mb-4">
                    <h3 class="h6 fw-bold">Attendance and leave</h3>
                    <p class="small text-secondary">Enter attended days, approved paid leave, and unpaid absences for this coverage.</p>
                    <form method="post" action="<?= site_url('payroll/payable-days'); ?>" id="employeeAttendanceForm" class="row g-3 align-items-end">
                        <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                        <input type="hidden" name="payroll_item_id" class="payroll-item-id">
                        <div class="col-12 col-md-4"><label for="modalAttendedDays" class="form-label">Days attended</label><input type="number" name="payable_days" id="modalAttendedDays" class="form-control" min="0" step="0.5" required></div>
                        <div class="col-12 col-md-4"><label for="modalPaidLeaveDays" class="form-label">Paid leave days</label><input type="number" name="paid_leave_days" id="modalPaidLeaveDays" class="form-control" min="0" step="0.5" required></div>
                        <div class="col-12 col-md-4"><label for="modalUnpaidAbsenceDays" class="form-label">Unpaid absence days</label><input type="number" name="absence_days" id="modalUnpaidAbsenceDays" class="form-control" min="0" step="0.5" required></div>
                        <div class="col-12"><button type="submit" class="btn btn-primary">Save attendance</button></div>
                    </form>
                </section>
                <hr>
                <section class="mb-4">
                    <h3 class="h6 fw-bold">Automatic taxes and contributions</h3>
                    <p class="small text-secondary">Calculated from the payroll settings effective for this pay period. Update the shared rules from the Payroll page.</p>
                    <div id="employeeStatutorySummary" class="list-group"></div>
                </section>
                <hr>
                <section>
                    <h3 class="h6 fw-bold">Other deductions</h3>
                    <div id="employeeDeductionList" class="mb-3"></div>
                    <form method="post" action="<?= site_url('payroll/deductions/add'); ?>" class="row g-3" id="employeeDeductionForm">
                        <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                        <input type="hidden" name="payroll_item_id" class="payroll-item-id">
                        <input type="hidden" name="category" value="other">
                        <div class="col-12 col-md-6"><label for="modalDeductionDescription" class="form-label">Description</label><input name="description" id="modalDeductionDescription" class="form-control" maxlength="150" placeholder="e.g. Salary advance" required></div>
                        <div class="col-12 col-md-6"><label for="modalDeductionAmount" class="form-label">Amount (PHP)</label><input type="number" name="amount" id="modalDeductionAmount" class="form-control" min="0.01" step="0.01" required></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-primary">Add deduction</button></div>
                    </form>
                    <p class="form-text mb-0">Use this only for non-statutory items such as a salary advance or approved one-time deduction.</p>
                </section>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Done</button></div>
        </div>
    </div>
</div>
<?php endif; ?>
<script>
(function () {
    const input = document.getElementById('payrollEmployeeSearch');
    const rows = Array.from(document.querySelectorAll('[data-payroll-employee]'));
    const count = document.getElementById('payrollEmployeeCount');
    if (!input) return;
    input.addEventListener('input', function () {
        const query = input.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(function (row) {
            const matches = row.dataset.payrollEmployee.includes(query);
            row.classList.toggle('d-none', !matches);
            if (matches) visible++;
        });
        if (count) count.textContent = visible;
    });
})();

document.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    if (!button || !button.classList.contains('edit-payroll-button')) return;
    const modal = event.target;
    const itemId = button.dataset.itemId;
    document.getElementById('employeePayrollIdentity').textContent = button.dataset.employeeName + ' · Employee no. ' + button.dataset.employeeNo;
    modal.querySelectorAll('.payroll-item-id').forEach(function (input) { input.value = itemId; });
    const days = button.dataset.periodDays;
    ['modalAttendedDays', 'modalPaidLeaveDays', 'modalUnpaidAbsenceDays'].forEach(function (id) {
        const input = document.getElementById(id);
        input.max = days;
    });
    document.getElementById('modalAttendedDays').value = button.dataset.attended;
    document.getElementById('modalPaidLeaveDays').value = button.dataset.paidLeave;
    document.getElementById('modalUnpaidAbsenceDays').value = button.dataset.unpaidAbsence;
    const statutory = document.getElementById('employeeStatutorySummary');
    statutory.replaceChildren();
    [
        ['SSS', button.dataset.sss],
        ['PhilHealth', button.dataset.philhealth],
        ['Pag-IBIG', button.dataset.pagibig],
        ['Withholding tax', button.dataset.withholding]
    ].forEach(function (entry) {
        const row = document.createElement('div');
        row.className = 'list-group-item d-flex justify-content-between align-items-center';
        const label = document.createElement('span');
        label.textContent = entry[0];
        const amount = document.createElement('strong');
        amount.textContent = 'PHP ' + Number(entry[1] || 0).toFixed(2);
        row.append(label, amount);
        statutory.appendChild(row);
    });

    const list = document.getElementById('employeeDeductionList');
    list.replaceChildren();
    const items = JSON.parse(button.dataset.deductions || '[]');
    if (Number(button.dataset.absenceDeduction) > 0) {
        const absence = document.createElement('div');
        absence.className = 'alert alert-warning py-2';
        absence.textContent = 'Unpaid absence deduction: PHP ' + Number(button.dataset.absenceDeduction).toFixed(2);
        list.appendChild(absence);
    }
    if (!items.length && Number(button.dataset.absenceDeduction) <= 0) {
        list.innerHTML = '<p class="small text-secondary mb-0">No other deductions entered for this employee yet.</p>';
    }
    if (items.length) {
        const table = document.createElement('div');
        table.className = 'list-group';
        items.forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'list-group-item d-flex justify-content-between align-items-center gap-2';
            const label = document.createElement('span');
            label.textContent = item.category.replace(/_/g, ' ') + ' · ' + item.description;
            const amount = document.createElement('strong');
            amount.textContent = 'PHP ' + Number(item.amount).toFixed(2);
            row.append(label, amount);
            table.appendChild(row);
        });
        list.appendChild(table);
    }
});
</script>
