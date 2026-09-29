<?php
$payment_modes = [
    'tri_term' => 'Tri-Term',
    'semi_annual' => 'Semi-Annual',
    'annual' => 'Annual'
];
$grade = (string) $configuration->grade_code;
$grade_label = $grade === 'N'
    ? 'Nursery'
    : ($grade === 'K' ? 'Kindergarten' : 'Grade ' . $grade);
$fee_groups = $fees ? [
    'Tuition' => ['items' => $fees['tuition'], 'amount_field' => 'tuition_fee'],
    'Worktext' => ['items' => $fees['worktext'], 'amount_field' => 'amount'],
    'Extra-curricular' => ['items' => $fees['extra_curricular'], 'amount_field' => 'amount']
] : [];
?>

<div class="container-fluid finance-configuration-details">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">
                <?= html_escape($configuration->school_year); ?>
                <span class="text-muted">·</span>
                <?= html_escape($grade_label); ?>
            </h2>
            <p class="text-muted mb-0">
                <?= html_escape($payment_modes[$configuration->payment_mode] ?? $configuration->payment_mode); ?> payment schedule
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('finance'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Back to configurations
            </a>
            <a href="<?= site_url('finance/configuration/edit/' . (int) $configuration->id); ?>" class="btn btn-primary">
                <i class="fas fa-edit me-1" aria-hidden="true"></i>Edit configuration
            </a>
        </div>
    </div>

    <?php foreach (['success', 'error'] as $flash_type): ?>
        <?php if ($this->session->flashdata($flash_type)): ?>
            <div class="alert alert-<?= $flash_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?= html_escape($this->session->flashdata($flash_type)); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-sm-row justify-content-between gap-3">
            <div>
                <div class="small text-muted">Configuration status</div>
                <?php $status = strtolower((string) $configuration->status); ?>
                <span class="badge text-bg-<?= $status === 'active' ? 'success' : ($status === 'draft' ? 'warning' : 'secondary'); ?>">
                    <?= html_escape(ucfirst($status)); ?>
                </span>
            </div>
            <div>
                <div class="small text-muted">Created</div>
                <div class="fw-semibold"><?= html_escape($configuration->created_at ?: '—'); ?></div>
            </div>
        </div>
    </div>

    <?php if (!$fees): ?>
        <div class="alert alert-info" role="status">
            This configuration is not active, so it has no active fee schedule to display.
        </div>
    <?php else: ?>
        <?php foreach ($fee_groups as $title => $group): ?>
            <?php $total = 0.0; ?>
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><?= html_escape($title); ?></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-3">Payment</th>
                                <th scope="col">Date</th>
                                <th scope="col" class="text-end px-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($group['items'])): ?>
                                <?php foreach ($group['items'] as $item): ?>
                                    <?php $amount = (float) ($item->{$group['amount_field']} ?? 0); $total += $amount; ?>
                                    <tr>
                                        <td class="px-3"><?= html_escape($item->payment_label); ?></td>
                                        <td><?= !empty($item->payment_date) ? html_escape($item->payment_date) : '—'; ?></td>
                                        <td class="text-end px-3">₱<?= number_format($amount, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No active schedule items.</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th scope="row" colspan="2" class="px-3">Total</th>
                                <th class="text-end px-3">₱<?= number_format($total, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>

        <?php foreach (['uniform_boys' => 'Boys uniforms', 'uniform_girls' => 'Girls uniforms'] as $key => $title): ?>
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><?= html_escape($title); ?></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-3">Size</th>
                                <th scope="col" class="text-end">Top</th>
                                <th scope="col" class="text-end">Bottom</th>
                                <th scope="col" class="text-end px-3">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($fees[$key])): ?>
                                <?php foreach ($fees[$key] as $uniform): ?>
                                    <?php $uniform_total = (float) $uniform->top_price + (float) $uniform->bottom_price; ?>
                                    <tr>
                                        <td class="px-3"><?= html_escape($uniform->uniform_size); ?></td>
                                        <td class="text-end">₱<?= number_format((float) $uniform->top_price, 2); ?></td>
                                        <td class="text-end">₱<?= number_format((float) $uniform->bottom_price, 2); ?></td>
                                        <td class="text-end px-3">₱<?= number_format($uniform_total, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No active uniform prices.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
