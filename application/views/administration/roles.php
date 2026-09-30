<div class="alert alert-warning">Access is currently enforced by role IDs in the application. The database labels IDs 4 and 5 as Parent and Finance, while application checks use those IDs in the opposite order. The role status flag is also not currently enforced. Resolve this mapping before editing roles or user assignments.</div>
<section class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">System roles</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">Role</th><th>Description</th><th>Accounts</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($roles as $role): ?>
                <tr><td class="ps-3"><span class="text-secondary me-2">#<?= (int) $role->id; ?></span><strong><?= html_escape($role->role_name); ?></strong></td><td><?= html_escape($role->description ?: '—'); ?></td><td><?= number_format((int) $role->user_count); ?></td><td><span class="badge text-bg-<?= $role->is_active ? 'success' : 'secondary'; ?>"><?= $role->is_active ? 'Active' : 'Inactive'; ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
