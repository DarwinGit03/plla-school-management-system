<?php if (!empty($success_message)): ?><div class="alert alert-success"><?= html_escape($success_message); ?></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger"><?= html_escape($error_message); ?></div><?php endif; ?>
<section class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Record year-end decisions</h2></div>
    <div class="card-body">
        <p class="text-secondary">Each save adds a decision to history. Recording a decision does not create the next school-year enrollment.</p>
        <form method="get" class="row g-3 align-items-end mb-3">
            <div class="col-12 col-md-4"><label class="form-label" for="promotionYear">School year</label><select class="form-select" name="school_year" id="promotionYear"><?php foreach ($years as $year): ?><option value="<?= html_escape($year); ?>" <?= $year === $school_year ? 'selected' : ''; ?>><?= html_escape($year); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Load students</button></div>
        </form>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Student</th><th>Grade</th><th>Decision</th><th>Next grade</th><th>Decision date</th><th>Notes</th><th></th></tr></thead>
            <tbody>
            <?php if (!$enrollments): ?><tr><td colspan="7" class="text-center text-secondary py-4">No enrollments found for this school year.</td></tr>
            <?php else: foreach ($enrollments as $enrollment): $formId = 'promotionForm' . (int) $enrollment->enrollment_id; ?>
                <tr>
                    <td><form id="<?= $formId; ?>" method="post" action="<?= site_url('academic/promotions/save'); ?>"><input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>"><input type="hidden" name="enrollment_id" value="<?= (int) $enrollment->enrollment_id; ?>"></form><?= html_escape(trim($enrollment->first_name . ' ' . $enrollment->last_name)); ?><div class="small text-secondary"><?= html_escape($enrollment->student_no); ?></div></td>
                    <td><?= html_escape($enrollment->grade_level); ?></td>
                    <td><select form="<?= $formId; ?>" class="form-select form-select-sm" name="decision" required><option value="promoted">Promoted</option><option value="retained">Retained</option><option value="transferred">Transferred</option></select></td>
                    <td><input form="<?= $formId; ?>" class="form-control form-control-sm" name="next_grade_code" maxlength="30" placeholder="Required if promoted"></td>
                    <td><input form="<?= $formId; ?>" class="form-control form-control-sm" type="date" name="decision_date" value="<?= date('Y-m-d'); ?>" required></td>
                    <td><input form="<?= $formId; ?>" class="form-control form-control-sm" name="notes" maxlength="500" placeholder="Optional"></td>
                    <td><button form="<?= $formId; ?>" type="submit" class="btn btn-sm btn-primary">Record decision</button></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </div>
</section>
<section class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Saved decision history</h2></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Student no.</th><th>Student</th><th>School year</th><th>Decision</th><th>Next grade</th><th>Date</th><th>Notes</th></tr></thead>
        <tbody>
        <?php if (!$promotion_history): ?><tr><td colspan="7" class="text-center text-secondary py-4">No decisions have been recorded for this year.</td></tr>
        <?php else: foreach ($promotion_history as $history): ?><tr><td><?= html_escape($history->student_no); ?></td><td><?= html_escape(trim($history->first_name . ' ' . $history->last_name)); ?></td><td><?= html_escape($history->school_year); ?></td><td><?= html_escape(ucfirst($history->decision)); ?></td><td><?= html_escape($history->next_grade_code ?: '—'); ?></td><td><?= html_escape($history->decision_date); ?></td><td><?= html_escape($history->notes ?: '—'); ?></td></tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
</section>
