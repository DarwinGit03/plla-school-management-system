<?php
$studentName = trim(implode(' ', array_filter([
    $student->first_name, $student->middle_name, $student->last_name, $student->suffix
], function ($part) { return trim((string) $part) !== ''; })));
?>
<div class="mb-3"><a href="<?= site_url('parent-portal'); ?>" class="text-decoration-none">&larr; Back to my children</a></div>
<section class="card soft-card mb-4">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><div class="small text-secondary">Student no. <?= html_escape($student->student_no); ?></div><h2 class="h4 fw-bold mb-0"><?= html_escape($studentName); ?></h2></div>
        <div class="student-mark" aria-hidden="true"><?= html_escape(strtoupper(substr((string) $student->first_name, 0, 1))); ?></div>
    </div>
</section>

<section class="card soft-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div><h3 class="h5 fw-bold mb-1">School year records</h3><p class="text-secondary mb-0">Choose a year to review this child’s approved results, attendance, and payments.</p></div>
            <?php if (!empty($enrollments)): ?>
                <form method="get" action="<?= site_url('parent-portal/child/' . (int) $student->id); ?>" class="d-flex flex-wrap align-items-end gap-2">
                    <div><label class="form-label small fw-semibold mb-1" for="enrollment_id">School year</label>
                        <select class="form-select" id="enrollment_id" name="enrollment_id">
                            <?php foreach ($enrollments as $enrollment): ?>
                                <option value="<?= (int) $enrollment->id; ?>" <?= $selected_enrollment && (int) $selected_enrollment->id === (int) $enrollment->id ? 'selected' : ''; ?>><?= html_escape($enrollment->academic_year . ' · ' . $enrollment->grade_level . ' · ' . $enrollment->section_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">View year</button>
                </form>
            <?php endif; ?>
        </div>
        <?php if (!$enrollments): ?><div class="alert alert-light border mt-3 mb-0">No active or completed school-year enrollment is available for this student yet.</div><?php endif; ?>
    </div>
</section>

<?php if ($selected_enrollment && $card): ?>
    <section class="mb-4">
        <?php $this->load->view('academic/report_card_document', ['card' => $card, 'grade_label' => $grade_label]); ?>
    </section>

    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div><h3 class="h5 fw-bold mb-1">Fees and balance</h3><p class="text-secondary mb-0">Totals for <?= html_escape($selected_enrollment->academic_year); ?>. Voided payments do not reduce the balance.</p></div>
            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('parent-portal/payments?student_id=' . (int) $student->id); ?>">View all this child’s payments</a>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4"><div class="card soft-card h-100"><div class="card-body"><div class="small text-secondary">Total assessed</div><div class="h4 fw-bold mb-0">PHP <?= number_format((float) $finance_totals['assessed'], 2); ?></div></div></div></div>
            <div class="col-12 col-md-4"><div class="card soft-card h-100"><div class="card-body"><div class="small text-secondary">Paid</div><div class="h4 fw-bold text-success mb-0">PHP <?= number_format((float) $finance_totals['paid'], 2); ?></div></div></div></div>
            <div class="col-12 col-md-4"><div class="card soft-card h-100"><div class="card-body"><div class="small text-secondary">Remaining balance</div><div class="h4 fw-bold text-warning-emphasis mb-0">PHP <?= number_format((float) $finance_totals['balance'], 2); ?></div></div></div></div>
        </div>
        <div class="card soft-card"><div class="card-header bg-white py-3"><div class="fw-bold">Fee items</div><div class="small text-secondary">Unpaid and overdue items are highlighted. A partial payment still leaves a balance due.</div></div><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="px-3">Category</th><th>Fee item</th><th>Due date</th><th>Status</th><th class="text-end">Assessed</th><th class="text-end">Paid</th><th class="text-end px-3">Balance</th></tr></thead>
            <tbody>
                <?php $parentFeeCategoryRowspans = []; $parentFeeCategoryShown = []; foreach ($fee_items as $categoryFee) { $categoryKey = strtolower(trim((string) $categoryFee->fee_category)); $parentFeeCategoryRowspans[$categoryKey] = ($parentFeeCategoryRowspans[$categoryKey] ?? 0) + 1; } ?>
                <?php if (!empty($fee_items)): foreach ($fee_items as $item): ?>
                    <?php
                    $itemBalance = (float) $item->balance;
                    $itemPaid = (float) $item->amount_paid;
                    $isOverdue = $itemBalance > 0 && !empty($item->payment_date) && $item->payment_date < date('Y-m-d');
                    $feeStatus = $itemBalance <= 0 ? 'Paid' : ($isOverdue ? 'Overdue' : ($itemPaid > 0 ? 'Partially paid' : 'Unpaid'));
                    $feeStatusClass = $itemBalance <= 0 ? 'success' : ($isOverdue ? 'danger' : ($itemPaid > 0 ? 'warning text-dark' : 'secondary'));
                    $itemCategoryKey = strtolower(trim((string) $item->fee_category));
                    $showItemCategory = !isset($parentFeeCategoryShown[$itemCategoryKey]);
                    if ($showItemCategory) $parentFeeCategoryShown[$itemCategoryKey] = true;
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : ($itemBalance > 0 ? 'table-warning' : ''); ?>"><?php if ($showItemCategory): ?><td class="px-3 fw-semibold align-middle" rowspan="<?= (int) $parentFeeCategoryRowspans[$itemCategoryKey]; ?>"><?= html_escape(ucwords(str_replace('_', ' ', $item->fee_category))); ?></td><?php endif; ?>
                        <td><?= html_escape($item->payment_label); ?></td>
                        <td><?= $item->payment_date ? html_escape($item->payment_date) : '&mdash;'; ?></td>
                        <td><span class="badge text-bg-<?= $feeStatusClass; ?>"><?= html_escape($feeStatus); ?></span></td>
                        <td class="text-end">PHP <?= number_format((float) $item->amount_due, 2); ?></td>
                        <td class="text-end">PHP <?= number_format((float) $item->amount_paid, 2); ?></td>
                        <td class="text-end px-3 fw-semibold">PHP <?= number_format((float) $item->balance, 2); ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="7" class="text-center text-secondary py-4">No fee assessments are available for this enrollment.</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </section>

    <section class="card soft-card">
        <div class="card-header bg-white py-3"><h3 class="h5 fw-bold mb-1">Payment history for <?= html_escape($selected_enrollment->academic_year); ?></h3><div class="small text-secondary">Receipts recorded for this child’s selected school year.</div></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="px-3">Date</th><th>Fee item</th><th>Receipt</th><th>Method</th><th>Status</th><th class="text-end px-3">Amount</th></tr></thead>
            <tbody>
                <?php if (!empty($payments)): foreach ($payments as $payment): ?>
                    <tr><td class="px-3"><?= html_escape($payment->paid_at); ?></td>
                        <td><?= html_escape(ucwords(str_replace('_', ' ', $payment->fee_category))); ?><div class="small text-secondary"><?= html_escape($payment->payment_label); ?></div></td>
                        <td><?= html_escape($payment->receipt_number); ?><?php if ($payment->reference_number): ?><div class="small text-secondary">Ref: <?= html_escape($payment->reference_number); ?></div><?php endif; ?></td>
                        <td><?= html_escape(ucwords(str_replace('_', ' ', $payment->payment_method))); ?></td>
                        <td><span class="badge text-bg-<?= $payment->status === 'posted' ? 'success' : 'secondary'; ?>"><?= html_escape(ucfirst($payment->status)); ?></span><?php if ($payment->status === 'voided' && $payment->void_reason): ?><div class="small text-secondary mt-1"><?= html_escape($payment->void_reason); ?></div><?php endif; ?></td>
                        <td class="text-end px-3 fw-semibold">PHP <?= number_format((float) $payment->amount_paid, 2); ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="6" class="text-center text-secondary py-4">No payments are recorded for this child in this school year.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
<?php endif; ?>
