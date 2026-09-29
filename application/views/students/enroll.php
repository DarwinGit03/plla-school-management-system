<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Enrollment</h1>
        <p class="text-muted mb-0">Search existing student records and review their current school year, grade, and section.</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form
                method="get"
                action="<?= site_url('students/enroll'); ?>"
                class="js-enrollment-search"
                data-grade-url="<?= site_url('students/grade_levels'); ?>"
                data-section-url="<?= site_url('students/sections'); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label for="search" class="form-label small fw-semibold">Student</label>
                        <input id="search" type="search" name="search" value="<?= html_escape($filters['search']); ?>" class="form-control" placeholder="Name, student number, or LRN">
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="academic_year" class="form-label small fw-semibold">School Year</label>
                        <select name="academic_year" id="academic_year" class="form-select">
                            <option value="">All years</option>
                            <?php foreach ($academic_years as $year): ?>
                                <option value="<?= html_escape($year->year); ?>" <?= (string) $filters['academic_year'] === (string) $year->year ? 'selected' : ''; ?>><?= html_escape($year->year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="grade_level" class="form-label small fw-semibold">Grade</label>
                        <select name="grade_level" id="grade_level" class="form-select" <?= empty($filters['academic_year']) ? 'disabled' : ''; ?>>
                            <option value="">All grades</option>
                            <?php foreach ($grade_levels as $grade): ?>
                                <option value="<?= html_escape($grade->grade); ?>" <?= (string) $filters['grade_level'] === (string) $grade->grade ? 'selected' : ''; ?>><?= html_escape($grade->grade); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="section" class="form-label small fw-semibold">Section</label>
                        <select name="section" id="section" class="form-select" <?= empty($filters['academic_year']) || empty($filters['grade_level']) ? 'disabled' : ''; ?>>
                            <option value="">All sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= html_escape($section->section); ?>" <?= (string) $filters['section'] === (string) $section->section ? 'selected' : ''; ?>><?= html_escape($section->section); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="status" class="form-label small fw-semibold">Student status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-2">
                        <label for="payment_mode" class="form-label small fw-semibold">Payment mode</label>
                        <select name="payment_mode" id="payment_mode" class="form-select">
                            <option value="">All modes</option>
                            <?php foreach ($payment_modes as $mode => $label): ?>
                                <option value="<?= html_escape($mode); ?>" <?= $filters['payment_mode'] === $mode ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-10 d-flex align-items-end justify-content-end gap-2">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i> Search</button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('students/enroll'); ?>">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Student records</strong>
            <span class="text-muted small"><?= number_format($total_students); ?> found</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 student-table">
                <thead class="table-light">
                    <tr><th>Student No.</th><th>Student</th><th>School Year</th><th>Grade</th><th>Section</th><th>Payment mode</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= html_escape($student->student_no); ?></td>
                                <td><?= html_escape(trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name . ' ' . $student->suffix)); ?></td>
                                <td><?= html_escape($student->academic_year ?: '—'); ?></td>
                                <td><?= html_escape($student->grade_level ?: '—'); ?></td>
                                <td><?= html_escape($student->section ?: '—'); ?></td>
                                <td><?= html_escape($payment_modes[$student->payment_mode] ?? 'â€”'); ?></td>
                                <td><span class="badge text-bg-<?= $student->status === 'active' ? 'success' : 'secondary'; ?>"><?= html_escape(ucfirst($student->status)); ?></span></td>
                                <td class="text-end"><a class="btn btn-sm btn-primary" href="<?= site_url('students/enroll/' . (int) $student->id); ?>">Enroll now</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No students match these filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="small text-muted">Page <?= (int) $current_page; ?> of <?= (int) $total_pages; ?></span>
                <div class="btn-group">
                    <?php if ($current_page > 1): ?><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('students/enroll?' . http_build_query(array_merge($filters, ['page' => $current_page - 1]))); ?>">Previous</a><?php endif; ?>
                    <?php if ($current_page < $total_pages): ?><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('students/enroll?' . http_build_query(array_merge($filters, ['page' => $current_page + 1]))); ?>">Next</a><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/js/student/enrollment.js'); ?>"></script>
