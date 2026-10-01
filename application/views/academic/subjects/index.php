<?php $csrfName = $this->security->get_csrf_token_name(); $csrfHash = $this->security->get_csrf_hash(); ?>
<?php if (!empty($success_message)): ?><div class="alert alert-success alert-dismissible fade show" role="status"><?= html_escape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= html_escape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>

<section class="card border-0 shadow-sm mb-4" id="subject-catalog">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-bold mb-1"><?= $edit_subject ? 'Edit subject' : 'Create subject'; ?></h2>
        <p class="small text-secondary mb-0">Choose a school year and grade, then enter the subject details. Assign a teacher below.</p>
    </div>
    <div class="card-body">
        <?php if (!$years): ?>
            <div class="alert alert-warning mb-0">Set up a school year and grade in Finance fee configurations before creating subject offerings.</div>
        <?php else: ?>
        <form method="post" action="<?= site_url('academic/subjects/save'); ?>" class="row g-3 align-items-end mb-4">
            <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
            <?php if ($edit_subject): ?><input type="hidden" name="id" value="<?= (int) $edit_subject->id; ?>"><?php endif; ?>
            <div class="col-12 col-md-3"><label for="subjectSchoolYear" class="form-label">School year <span class="text-danger">*</span></label><select id="subjectSchoolYear" name="school_year" class="form-select" required><option value="">Select year</option><?php foreach ($years as $schoolYear): ?><option value="<?= html_escape($schoolYear); ?>" <?= ($edit_subject->school_year ?? ($years[0] ?? '')) === $schoolYear ? 'selected' : ''; ?>><?= html_escape($schoolYear); ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-3"><label for="subjectGrade" class="form-label">Grade <span class="text-danger">*</span></label><select id="subjectGrade" name="grade_code" class="form-select" required><option value="">Select grade</option><?php foreach ($grade_options_by_year as $yearValue => $grades): foreach ($grades as $gradeCode): ?><option data-school-year="<?= html_escape($yearValue); ?>" value="<?= html_escape($gradeCode); ?>" <?= ($edit_subject->school_year ?? '') === $yearValue && ($edit_subject->grade_code ?? '') === $gradeCode ? 'selected' : ''; ?>><?= html_escape($grade_labels[$gradeCode] ?? $gradeCode); ?></option><?php endforeach; endforeach; ?></select></div>
            <div class="col-12 col-md-2"><label for="subjectCode" class="form-label">Subject code <span class="text-secondary">(optional)</span></label><input id="subjectCode" name="subject_code" class="form-control" maxlength="30" value="<?= html_escape($edit_subject->subject_code ?? ''); ?>" placeholder="e.g. ENG-1"></div>
            <div class="col-12 col-md-4"><label for="subjectName" class="form-label">Subject name <span class="text-danger">*</span></label><input id="subjectName" name="subject_name" class="form-control" maxlength="120" value="<?= html_escape($edit_subject->subject_name ?? ''); ?>" placeholder="e.g. English" required></div>
            <div class="col-12"><label for="subjectDescription" class="form-label">Description <span class="text-secondary">(optional)</span></label><textarea id="subjectDescription" name="description" class="form-control" maxlength="500" rows="2" placeholder="Short subject details"><?= html_escape($edit_subject->description ?? ''); ?></textarea></div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <?php if ($edit_subject): ?><a class="btn btn-outline-secondary" href="<?= site_url('academic/subjects#subject-catalog'); ?>">Cancel edit</a><?php endif; ?>
                <button type="submit" class="btn btn-primary"><?= $edit_subject ? 'Save subject' : 'Create subject'; ?></button>
            </div>
        </form>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>School year</th><th>Grade</th><th>Code</th><th>Subject</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if (!$subjects): ?><tr><td colspan="7" class="text-center text-secondary py-4">No subjects added yet.</td></tr>
                    <?php else: foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= html_escape($subject->school_year ?: '—'); ?></td>
                            <td><?= html_escape($grade_labels[$subject->grade_code] ?? $subject->grade_code ?? '—'); ?></td>
                            <td><?= html_escape($subject->subject_code ?: '—'); ?></td>
                            <td class="fw-semibold"><?= html_escape($subject->subject_name); ?></td>
                            <td><?= html_escape($subject->description ?: '—'); ?></td>
                            <td><span class="badge text-bg-<?= $subject->is_active ? 'success' : 'secondary'; ?>"><?= $subject->is_active ? 'Active' : 'Inactive'; ?></span></td>
                            <td><div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('academic/subjects?edit=' . (int) $subject->id . '#subject-catalog'); ?>">Edit</a>
                                <form method="post" action="<?= site_url('academic/subjects/status'); ?>" onsubmit="return confirm('<?= $subject->is_active ? 'Deactivate' : 'Activate'; ?> this subject?');">
                                    <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>"><input type="hidden" name="id" value="<?= (int) $subject->id; ?>"><input type="hidden" name="is_active" value="<?= $subject->is_active ? 0 : 1; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $subject->is_active ? 'Deactivate' : 'Activate'; ?></button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4" id="assignments">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-bold mb-1">Assign a teacher</h2>
        <p class="small text-secondary mb-0">Each subject offering can have one teacher across all sections for its grade.</p>
    </div>
    <div class="card-body">
        <?php if (!$years): ?>
            <div class="alert alert-warning mb-0">Set up a school year and grade in Finance fee configurations before making teacher assignments.</div>
        <?php elseif (!$active_subjects): ?>
            <div class="alert alert-info mb-0">Create and activate an unassigned subject offering above before assigning a teacher.</div>
        <?php elseif (!$employees): ?>
            <div class="alert alert-info mb-0">Register an active teacher in the Faculty module before making assignments.</div>
        <?php else: ?>
            <form method="post" action="<?= site_url('academic/subjects/assign'); ?>" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                <div class="col-12 col-md-6"><label for="assignmentSubject" class="form-label">Subject offering</label><select id="assignmentSubject" name="subject_id" class="form-select" required><option value="">Select subject</option><?php foreach ($active_subjects as $subject): $subjectLabel = trim(($subject->subject_code ? $subject->subject_code . ' · ' : '') . $subject->subject_name . ' · ' . $subject->school_year . ' · ' . ($grade_labels[$subject->grade_code] ?? $subject->grade_code)); ?><option value="<?= (int) $subject->id; ?>"><?= html_escape($subjectLabel); ?></option><?php endforeach; ?></select></div>
                <div class="col-12 col-md-4"><label for="assignmentEmployee" class="form-label">Teacher</label><select id="assignmentEmployee" name="employee_id" class="form-select" required><option value="">Select teacher</option><?php foreach ($employees as $employee): $name = trim(implode(' ', array_filter([$employee->first_name, $employee->middle_name, $employee->last_name]))); ?><option value="<?= (int) $employee->id; ?>"><?= html_escape($employee->employee_no . ' - ' . $name); ?></option><?php endforeach; ?></select></div>
                <div class="col-12 col-md-2 d-grid"><button type="submit" class="btn btn-primary">Assign teacher</button></div>
            </form>
        <?php endif; ?>

        <form method="get" action="<?= site_url('academic/subjects'); ?>" class="row g-3 align-items-end mb-3">
            <div class="col-12 col-md-5"><label for="filterTeacher" class="form-label">Teacher</label><select id="filterTeacher" name="teacher" class="form-select"><option value="0">All teachers</option><?php foreach ($teachers as $employee): $name = trim(implode(' ', array_filter([$employee->first_name, $employee->middle_name, $employee->last_name]))); ?><option value="<?= (int) $employee->id; ?>" <?= $teacher_filter === (int) $employee->id ? 'selected' : ''; ?>><?= html_escape($name . ' · ' . $employee->employee_no); ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-5"><label for="filterSubject" class="form-label">Subject</label><select id="filterSubject" name="subject" class="form-select"><option value="0">All subjects</option><?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject->id; ?>" <?= $subject_filter === (int) $subject->id ? 'selected' : ''; ?>><?= html_escape($subject->subject_name . ' · ' . $subject->school_year . ' · ' . ($grade_labels[$subject->grade_code] ?? $subject->grade_code)); ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-2 d-flex gap-2"><button type="submit" class="btn btn-outline-primary flex-grow-1">Filter</button><a href="<?= site_url('academic/subjects#assignments'); ?>" class="btn btn-outline-secondary">Clear</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>School year</th><th>Grade</th><th>Subject</th><th>Teacher</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    <?php if (!$assignments): ?><tr><td colspan="6" class="text-center text-secondary py-4">No teacher assignments match these filters.</td></tr>
                    <?php else: foreach ($assignments as $assignment): $employeeName = trim(implode(' ', array_filter([$assignment->first_name, $assignment->middle_name, $assignment->last_name]))); ?>
                        <tr>
                            <td><?= html_escape($assignment->school_year); ?></td>
                            <td><?= html_escape($grade_labels[$assignment->grade_code] ?? $assignment->grade_code); ?></td>
                            <td class="fw-semibold"><?= html_escape($assignment->subject_name); ?><?php if ($assignment->subject_code): ?><div class="small text-secondary"><?= html_escape($assignment->subject_code); ?></div><?php endif; ?></td>
                            <td><?= html_escape($employeeName); ?><div class="small text-secondary"><?= html_escape($assignment->employee_no); ?><?php if ($assignment->position_title): ?> &middot; <?= html_escape($assignment->position_title); ?><?php endif; ?></div></td>
                            <td><span class="badge text-bg-<?= $assignment->status === 'active' ? 'success' : 'secondary'; ?>"><?= ucfirst(html_escape($assignment->status)); ?></span></td>
                            <td class="text-end"><form method="post" action="<?= site_url('academic/subjects/assignment-status'); ?>"><input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>"><input type="hidden" name="id" value="<?= (int) $assignment->id; ?>"><input type="hidden" name="status" value="<?= $assignment->status === 'active' ? 'inactive' : 'active'; ?>"><button type="submit" class="btn btn-sm btn-outline-secondary"><?= $assignment->status === 'active' ? 'Remove teacher' : 'Reactivate'; ?></button></form></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
