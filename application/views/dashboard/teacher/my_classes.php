<?php if (!$employee || $employee->status !== 'active'): ?>
    <section class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold">Employee profile not linked</h2>
            <p class="text-secondary mb-0">Your account is not linked to an active employee profile. Please contact the school administrator.</p>
        </div>
    </section>
<?php else: ?>
    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div><h2 class="h6 fw-bold mb-1">Teaching assignments</h2><p class="small text-secondary mb-0">Subjects assigned to you by school year and grade.</p></div>
                <form method="get" action="<?= site_url('my-classes'); ?>" class="d-flex align-items-end gap-2"><div><label class="form-label small mb-1" for="teacherClassYear">School year</label><select id="teacherClassYear" name="school_year" class="form-select form-select-sm"><option value="">All years</option><?php foreach ($school_years as $year): ?><option value="<?= html_escape($year); ?>" <?= $selected_school_year === $year ? 'selected' : ''; ?>><?= html_escape($year); ?></option><?php endforeach; ?></select></div><button class="btn btn-sm btn-outline-primary" type="submit">View</button></form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">School year</th>
                        <th>Grade</th>
                        <th>Subject</th>
                        <th>Subject code</th>
                        <th class="text-end pe-3">Class list</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$assignments): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No subjects have been assigned to you yet.</td>
                        </tr>
                    <?php else: foreach ($assignments as $assignment): ?>
                        <tr>
                            <td class="ps-3"><?= html_escape($assignment->school_year); ?></td>
                            <td><?= html_escape($assignment->grade_label); ?></td>
                            <td class="fw-semibold"><?= html_escape($assignment->subject_name); ?></td>
                            <td><?= html_escape($assignment->subject_code ?: '—'); ?></td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end flex-wrap gap-2"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('my-classes/' . (int) $assignment->id . '/attendance'); ?>">Attendance</a><a class="btn btn-sm btn-outline-primary" href="<?= site_url('my-classes/' . (int) $assignment->id); ?>">View students and grades</a></div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
