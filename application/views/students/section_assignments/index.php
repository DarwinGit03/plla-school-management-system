<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$selectedGradeLabel = $selected_grade !== ''
    ? $this->fee_configuration_model->grade_label($selected_grade)
    : '';
?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= html_escape($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= html_escape($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-lg-4">
        <div class="mb-3">
            <h2 class="h5 fw-bold mb-1">Assign students to a section</h2>
            <p class="text-secondary small mb-0">Choose a school year and grade to see every active enrollment and its current section.</p>
        </div>

        <form method="get" action="<?= site_url('students/section-assignments'); ?>" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="assignmentYear" class="form-label small fw-semibold">School year</label>
                <select name="year" id="assignmentYear" class="form-select" required>
                    <option value="">Select school year</option>
                    <?php foreach ($school_years as $year): ?>
                        <option value="<?= html_escape($year); ?>" <?= $selected_year === $year ? 'selected' : ''; ?>><?= html_escape($year); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="assignmentGrade" class="form-label small fw-semibold">Grade</label>
                <select name="grade" id="assignmentGrade" class="form-select" <?= $selected_year === '' ? 'disabled' : ''; ?> required>
                    <option value="">Select grade</option>
                    <?php foreach ($grades as $grade): ?>
                        <option value="<?= html_escape($grade); ?>" <?= $selected_grade === $grade ? 'selected' : ''; ?>><?= html_escape($this->fee_configuration_model->grade_label($grade)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="assignmentSearch" class="form-label small fw-semibold">Find student</label>
                <input type="search" name="q" id="assignmentSearch" value="<?= html_escape($search); ?>" class="form-control" placeholder="Name, student no. or LRN">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1" aria-hidden="true"></i>Show students</button>
                <a href="<?= site_url('students/section-assignments'); ?>" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</section>

<?php if ($selected_year === '' || $selected_grade === ''): ?>
    <div class="alert alert-info" role="status">Choose a school year and grade to display active enrollments.</div>
<?php else: ?>
    <?php if (empty($sections)): ?>
        <div class="alert alert-warning" role="status">
            No active sections are configured for this grade.
            <a class="alert-link" href="<?= site_url('academic/sections?year=' . rawurlencode($selected_year) . '&grade=' . rawurlencode($selected_grade)); ?>">Create a section</a> first.
        </div>
    <?php endif; ?>

    <section class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
            <div>
                <h2 class="h6 fw-bold mb-1">Students in <?= html_escape($selectedGradeLabel); ?></h2>
                <div class="small text-secondary"><?= html_escape($selected_year); ?> · Current section is shown for each student.</div>
            </div>
            <span class="badge text-bg-light border text-secondary"><?= count($students); ?> active <?= count($students) === 1 ? 'student' : 'students'; ?></span>
        </div>
        <form method="post" action="<?= site_url('students/section-assignments/assign'); ?>" id="sectionAssignmentForm">
            <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
            <input type="hidden" name="year" value="<?= html_escape($selected_year); ?>">
            <input type="hidden" name="grade" value="<?= html_escape($selected_grade); ?>">
            <input type="hidden" name="q" value="<?= html_escape($search); ?>">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 3rem"><input class="form-check-input" type="checkbox" id="selectAllStudents" aria-label="Select all students" <?= empty($students) || empty($sections) ? 'disabled' : ''; ?>></th>
                            <th>Student No.</th>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Section</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" class="py-5 text-center text-secondary">No active students match this school year, grade, and search.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <?php
                                $studentName = trim(implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name, $student->suffix])));
                                $hasSection = !empty($student->section_id) || trim((string) $student->current_section) !== '';
                                ?>
                                <tr>
                                    <td class="ps-3"><input class="form-check-input student-assignment-checkbox" type="checkbox" name="enrollment_ids[]" value="<?= (int) $student->enrollment_id; ?>" data-has-section="<?= $hasSection ? '1' : '0'; ?>" aria-label="Select <?= html_escape($studentName); ?>" <?= empty($sections) ? 'disabled' : ''; ?>></td>
                                    <td class="fw-semibold"><?= html_escape($student->student_no); ?></td>
                                    <td><?= html_escape($studentName); ?></td>
                                    <td><?= html_escape($this->fee_configuration_model->grade_label($student->grade_level)); ?></td>
                                    <td><?= $hasSection ? html_escape($student->current_section) : '<span class="text-secondary">Unassigned</span>'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
                <span class="small text-secondary align-self-lg-center"><span id="selectedStudentCount">0</span> selected</span>
                <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-end">
                    <div>
                        <label for="assignmentTargetSection" class="form-label small fw-semibold mb-1">Assign selected students to</label>
                        <select name="section_id" id="assignmentTargetSection" class="form-select" <?= empty($sections) ? 'disabled' : ''; ?> required>
                            <option value="">Choose section</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= (int) $section->id; ?>" <?= (int) $selected_section_id === (int) $section->id ? 'selected' : ''; ?>><?= html_escape($section->section); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" id="assignSelectedButton" disabled>
                        <i class="fas fa-users me-1" aria-hidden="true"></i><span id="assignSelectedButtonText">Assign selected</span>
                    </button>
                </div>
            </div>
        </form>
    </section>
    <p class="small text-secondary mb-0">Admin and principal users can move selected students to another section by choosing a new destination above.</p>
<?php endif; ?>
