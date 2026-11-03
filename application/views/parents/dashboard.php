<section class="card soft-card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div><h2 class="h5 fw-bold mb-1">Your linked students</h2><p class="text-secondary mb-0">Students connected to this guardian email in the school record.</p></div>
            <span class="badge rounded-pill text-bg-primary px-3 py-2"><?= count($students); ?> student<?= count($students) === 1 ? '' : 's'; ?></span>
        </div>
        <?php if (!empty($students)): ?>
            <div class="row g-3">
                <?php foreach ($students as $student): ?>
                    <?php $studentName = trim(implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name, $student->suffix], function ($part) { return trim((string) $part) !== ''; }))); ?>
                    <div class="col-12 col-lg-6">
                        <article class="border rounded-4 bg-white p-3 h-100">
                            <div class="d-flex gap-3">
                                <div class="student-mark" aria-hidden="true"><?= html_escape(strtoupper(substr($student->first_name, 0, 1))); ?></div>
                                <div class="flex-grow-1">
                                    <h3 class="h6 fw-bold mb-1"><?= html_escape($studentName); ?></h3>
                                    <div class="small text-secondary">Student no. <?= html_escape($student->student_no); ?></div>
                                    <?php if (!empty($student->enrollment_status)): ?>
                                        <div class="mt-3 d-flex flex-wrap gap-2">
                                            <span class="badge text-bg-light border"><?= html_escape($student->academic_year); ?></span>
                                            <span class="badge text-bg-light border"><?= html_escape($student->grade_level); ?></span>
                                            <?php if (!empty($student->section)): ?><span class="badge text-bg-light border"><?= html_escape($student->section); ?></span><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="small text-secondary mt-3 mb-0">No active enrollment is recorded.</p>
                                    <?php endif; ?>
                                    <a class="btn btn-sm btn-primary mt-3" href="<?= site_url('parent-portal/child/' . (int) $student->id); ?>">View this child’s records</a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-secondary">No students are currently linked to this email. Please contact the school office to update the guardian record.</div>
        <?php endif; ?>
    </div>
</section>
<div class="alert alert-primary mt-4 mb-0">Need help with a student record? Contact the school office so staff can verify and update the guardian information.</div>
