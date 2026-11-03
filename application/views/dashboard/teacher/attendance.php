<?php if (!empty($success_message)): ?><div class="alert alert-success"><?= html_escape($success_message); ?></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger"><?= html_escape($error_message); ?></div><?php endif; ?>
<section class="card border-0 shadow-sm mb-4"><div class="card-body d-flex flex-column flex-md-row justify-content-between gap-3">
    <div><div class="small text-secondary">Subject attendance</div><h2 class="h4 fw-bold mb-1"><?= html_escape($assignment->subject_name); ?></h2><div class="text-secondary"><?= html_escape($assignment->offering_school_year); ?> · Grade <?= html_escape($assignment->offering_grade_code); ?></div></div>
    <a class="btn btn-outline-secondary align-self-md-start" href="<?= site_url('my-classes/' . (int) $assignment->id); ?>">Back to class list</a>
</div></section>
<section class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-1">Attendance by section and date</h2><p class="small text-secondary mb-0">Each subject, section, and date has one attendance session. Changes after recording need reviewer approval.</p></div>
<div class="card-body"><form method="get" class="row g-3 align-items-end mb-4">
    <div class="col-12 col-md-5"><label class="form-label" for="attendanceSection">Section</label><select class="form-select" name="section_id" id="attendanceSection" required><?php foreach ($sections as $section): ?><option value="<?= (int) $section->id; ?>" <?= (int) $selected_section_id === (int) $section->id ? 'selected' : ''; ?>><?= html_escape($section->section); ?></option><?php endforeach; ?></select></div>
    <div class="col-12 col-md-4"><label class="form-label" for="attendanceDate">Date</label><input class="form-control" type="date" name="date" id="attendanceDate" max="<?= date('Y-m-d'); ?>" value="<?= html_escape($attendance_date); ?>" required></div>
    <div class="col-12 col-md-3"><button class="btn btn-primary w-100" type="submit">Load students</button></div>
</form>
<?php if (!$sections): ?><div class="alert alert-info mb-0">No active section currently has enrolled students for this subject.</div>
<?php elseif (!$students): ?><div class="alert alert-info mb-0">No active students are enrolled in this section.</div>
<?php else: ?>
<form method="post" action="<?= site_url('my-classes/' . (int) $assignment->id . '/attendance/save'); ?>">
    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
    <input type="hidden" name="section_id" value="<?= (int) $selected_section_id; ?>"><input type="hidden" name="attendance_date" value="<?= html_escape($attendance_date); ?>">
    <?php if ($session): ?><div class="alert alert-warning">Attendance was recorded. Any changed status below will be submitted as a correction request.</div><?php endif; ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-light"><tr><th>Student no.</th><th>Student</th><th>Attendance status</th><?php if ($session): ?><th>Current</th><?php endif; ?></tr></thead><tbody>
    <?php foreach ($students as $student): $record = $attendance[(int) $student->enrollment_id] ?? null; $name = trim(implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name]))); ?>
        <tr><td><?= html_escape($student->student_no ?: '—'); ?></td><td><?= html_escape($name); ?></td><td><select class="form-select" name="attendance[<?= (int) $student->enrollment_id; ?>]" required><option value="">Select status</option><?php foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $value => $label): ?><option value="<?= $value; ?>" <?= $record && $record->attendance_status === $value ? 'selected' : ''; ?>><?= $label; ?></option><?php endforeach; ?></select></td><?php if ($session): ?><td><?= html_escape(ucfirst($record->attendance_status ?? 'Missing')); ?></td><?php endif; ?></tr>
    <?php endforeach; ?></tbody></table></div>
    <?php if ($session): ?><div class="mb-3"><label class="form-label" for="attendanceReason">Reason for requested changes</label><textarea class="form-control" id="attendanceReason" name="correction_reason" maxlength="500" rows="2" placeholder="Required only when changing a recorded status"></textarea></div><?php endif; ?>
    <button class="btn btn-primary" type="submit"><?= $session ? 'Request attendance corrections' : 'Record attendance'; ?></button>
</form><?php endif; ?></div></section>
