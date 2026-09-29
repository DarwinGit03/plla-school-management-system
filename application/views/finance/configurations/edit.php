<?php
$mode_labels = ['tri_term' => 'Tri-Term', 'semi_annual' => 'Semi-Annual', 'annual' => 'Annual'];
$groups = [
    'tuition' => ['title' => 'Tuition', 'amount' => 'tuition_fee'],
    'worktext' => ['title' => 'Worktext', 'amount' => 'amount'],
    'extra_curricular' => ['title' => 'Extra-curricular', 'amount' => 'amount']
];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 fw-bold mb-1"><?= html_escape($configuration->school_year); ?> · Grade <?= html_escape($configuration->grade_code); ?></h2>
            <p class="text-muted mb-0"><?= html_escape($mode_labels[$configuration->payment_mode] ?? $configuration->payment_mode); ?> fee schedule</p>
        </div>
        <a href="<?= site_url('finance/view/' . (int) $configuration->id); ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <form method="post" action="<?= site_url('finance/configuration/edit/' . (int) $configuration->id); ?>">
        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <label for="configuration-status" class="form-label fw-semibold">Configuration status</label>
                <select name="status" id="configuration-status" class="form-select" required>
                    <?php foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label): ?>
                        <option value="<?= $value; ?>" <?= $configuration->status === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php foreach ($groups as $key => $group): ?>
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><?= html_escape($group['title']); ?> schedule</div>
                <?php if ($key !== 'tuition'): ?>
                    <div class="px-3 pt-3 small text-muted">This schedule is shared by payment modes for this school year and grade.</div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th class="px-3">Payment label</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($fees[$key] as $fee): ?>
                                <tr>
                                    <td class="px-3"><input class="form-control" name="fees[<?= $key; ?>][<?= (int) $fee->id; ?>][payment_label]" value="<?= html_escape($fee->payment_label); ?>" required></td>
                                    <td><input class="form-control" type="date" name="fees[<?= $key; ?>][<?= (int) $fee->id; ?>][payment_date]" value="<?= html_escape($fee->payment_date ?? ''); ?>"></td>
                                    <td><input class="form-control" type="number" min="0" step="0.01" name="fees[<?= $key; ?>][<?= (int) $fee->id; ?>][amount]" value="<?= html_escape($fee->{$group['amount']}); ?>" required></td>
                                    <td><div class="form-check"><input class="form-check-input" type="checkbox" name="fees[<?= $key; ?>][<?= (int) $fee->id; ?>][active]" value="1" <?= $fee->status === 'active' ? 'checked' : ''; ?>><label class="form-check-label">Active</label></div></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$fees[$key]): ?><tr><td colspan="4" class="text-center text-muted py-3">No fee items found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Uniform prices</div>
            <div class="px-3 pt-3 small text-muted">Uniform prices are shared across grades for this school year.</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th class="px-3">Type</th><th>Size</th><th>Top</th><th>Bottom</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($fees['uniforms'] as $uniform): ?>
                            <tr>
                                <td class="px-3 text-capitalize"><?= html_escape($uniform->uniform_type); ?></td>
                                <td><?= html_escape($uniform->uniform_size); ?></td>
                                <td><input class="form-control" type="number" min="0" step="0.01" name="uniforms[<?= (int) $uniform->id; ?>][top_price]" value="<?= html_escape($uniform->top_price); ?>" required></td>
                                <td><input class="form-control" type="number" min="0" step="0.01" name="uniforms[<?= (int) $uniform->id; ?>][bottom_price]" value="<?= html_escape($uniform->bottom_price); ?>" required></td>
                                <td><div class="form-check"><input class="form-check-input" type="checkbox" name="uniforms[<?= (int) $uniform->id; ?>][active]" value="1" <?= $uniform->status === 'active' ? 'checked' : ''; ?>><label class="form-check-label">Active</label></div></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$fees['uniforms']): ?><tr><td colspan="5" class="text-center text-muted py-3">No uniform prices found for this year.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="<?= site_url('finance/view/' . (int) $configuration->id); ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1" aria-hidden="true"></i>Save changes</button>
        </div>
    </form>
</div>
