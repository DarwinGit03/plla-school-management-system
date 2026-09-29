<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <strong>Recorded payments</strong>
            <span class="small text-muted"><?= number_format(count($payments)); ?> payments</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Date</th>
                        <th>Student</th>
                        <th>School Year</th>
                        <th>Fee Item</th>
                        <th>Receipt</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end px-3">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php $student_name = trim($payment->first_name . ' ' . $payment->middle_name . ' ' . $payment->last_name . ' ' . $payment->suffix); ?>
                            <tr>
                                <td class="px-3"><?= html_escape($payment->paid_at); ?></td>
                                <td>
                                    <div class="fw-semibold"><?= html_escape($student_name); ?></div>
                                    <small class="text-muted"><?= html_escape($payment->student_no); ?></small>
                                </td>
                                <td><?= html_escape($payment->academic_year); ?></td>
                                <td>
                                    <div><?= html_escape(ucwords(str_replace('_', ' ', $payment->fee_category))); ?></div>
                                    <small class="text-muted"><?= html_escape($payment->payment_label); ?></small>
                                </td>
                                <td>
                                    <div><?= html_escape($payment->receipt_number); ?></div>
                                    <?php if (!empty($payment->reference_number)): ?>
                                        <small class="text-muted">Ref: <?= html_escape($payment->reference_number); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= html_escape(ucwords(str_replace('_', ' ', $payment->payment_method))); ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $payment->status === 'posted' ? 'success' : 'secondary'; ?>">
                                        <?= html_escape(ucfirst($payment->status)); ?>
                                    </span>
                                    <?php if ($payment->status === 'voided' && !empty($payment->void_reason)): ?>
                                        <div class="small text-muted mt-1"><?= html_escape($payment->void_reason); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-3 fw-semibold">₱<?= number_format((float) $payment->amount_paid, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No payment records are available for your linked students.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
