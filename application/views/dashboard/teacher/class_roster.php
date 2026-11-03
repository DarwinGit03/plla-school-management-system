<?php if (!empty($success_message)): ?><div class="alert alert-success alert-dismissible fade show" role="status"><?= html_escape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= html_escape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <div class="text-secondary small">Subject</div>
            <h2 class="h4 fw-bold mb-2"><?= html_escape($assignment->subject_name); ?></h2>
            <div class="text-secondary">
                <?= html_escape($assignment->offering_school_year); ?>
                <span class="mx-1">·</span><?= html_escape($grade_label); ?>
                <?php if ($assignment->subject_code): ?><span class="mx-1">·</span><?= html_escape($assignment->subject_code); ?><?php endif; ?>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-md-start"><a href="<?= site_url('my-classes/' . (int) $assignment->id . '/attendance'); ?>" class="btn btn-primary">Take attendance</a><a href="<?= site_url('dashboard'); ?>" class="btn btn-outline-secondary">Back to dashboard</a></div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
                <h2 class="h6 fw-bold mb-1">Student grades</h2>
                <p class="small text-secondary mb-0"><?= count($students); ?> active student<?= count($students) === 1 ? '' : 's'; ?>. Enter grades from 0 to 100 and submit them for review.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">Student no.</th><th>Student</th><th>Section</th><th>Prelim</th><th>Midterm</th><th>Finals</th><th>Final grade</th><th class="text-end pe-3">Action</th></tr></thead>
            <tbody>
                <?php if (!$students): ?>
                    <tr><td colspan="8" class="text-center text-secondary py-4">No students are actively enrolled in this grade for this school year.</td></tr>
                <?php else: foreach ($students as $student): ?>
                    <?php $studentName = trim(implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name, $student->suffix]))); ?>
                    <?php $gradeRecord = $grades_by_enrollment[(int) $student->enrollment_id] ?? null; $formId = 'gradeForm' . (int) $student->enrollment_id; ?>
                    <tr id="student-<?= (int) $student->enrollment_id; ?>">
                        <td class="ps-3"><?= html_escape($student->student_no ?: '—'); ?></td>
                        <td class="fw-semibold"><?= html_escape($studentName); ?></td>
                        <td><?= html_escape($student->current_section ?: 'Not assigned'); ?></td>
                        <?php foreach (['prelim_grade' => 'Prelim', 'midterm_grade' => 'Midterm', 'finals_grade' => 'Finals', 'final_grade' => 'Final grade'] as $field => $label): ?>
                            <td>
                                <label class="visually-hidden" for="<?= $formId . ucfirst(str_replace('_grade', '', $field)); ?>"><?= html_escape($label . ' for ' . $studentName); ?></label>
                                <input
                                    id="<?= $formId . ucfirst(str_replace('_grade', '', $field)); ?>"
                                    form="<?= $formId; ?>"
                                    type="number"
                                    name="<?= $field; ?>"
                                    class="form-control form-control-sm"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    value="<?= $gradeRecord && $gradeRecord->{$field} !== null ? html_escape(number_format((float) $gradeRecord->{$field}, 2, '.', '')) : ''; ?>"
                                    placeholder="0-100"
                                    inputmode="decimal">
                            </td>
                        <?php endforeach; ?>
                        <td class="text-end pe-3">
                            <div class="small mb-2"><?php if (!$gradeRecord): ?><span class="badge text-bg-secondary">Not entered</span><?php elseif ($gradeRecord->review_status === 'approved'): ?><span class="badge text-bg-success">Approved</span><?php elseif ($gradeRecord->review_status === 'returned'): ?><span class="badge text-bg-warning">Returned for changes</span><?php else: ?><span class="badge text-bg-info">Pending review</span><?php endif; ?></div>
                            <form id="<?= $formId; ?>" method="post" action="<?= site_url('my-classes/' . (int) $assignment->id . '/grades'); ?>">
                                <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                <input type="hidden" name="enrollment_id" value="<?= (int) $student->enrollment_id; ?>">
                                <?php if ($gradeRecord && $gradeRecord->review_status === 'approved'): ?><textarea class="form-control form-control-sm mb-2" name="correction_reason" rows="2" maxlength="500" placeholder="Reason required for approved grade changes"></textarea><?php endif; ?>
                            </form>
                            <button type="submit" form="<?= $formId; ?>" class="btn btn-sm <?= $gradeRecord && $gradeRecord->review_status === 'approved' ? 'btn-outline-primary' : 'btn-primary'; ?>"><?= $gradeRecord && $gradeRecord->review_status === 'approved' ? 'Request correction' : 'Submit for review'; ?></button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
