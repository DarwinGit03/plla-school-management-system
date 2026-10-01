<?php
$quickLinks = [];
if (has_permission('students.view')) $quickLinks[] = ['Student list', 'Browse student profiles', 'students', 'fa-user-graduate', 'blue'];
if (has_permission('students.manage')) $quickLinks[] = ['Register student', 'Add a student profile', 'students/create', 'fa-user-plus', 'purple'];
if (has_permission('students.enrollment.manage')) $quickLinks[] = ['Enroll student', 'Set year, grade, and section', 'students/enroll', 'fa-clipboard-check', 'teal'];
if (has_permission('faculty.view') || has_permission('faculty.manage')) $quickLinks[] = ['Faculty and employees', 'View employee profiles', 'teachers', 'fa-chalkboard-user', 'orange'];
if (has_permission('finance.payments.manage')) $quickLinks[] = ['Record payment', 'Post a student payment', 'finance/payments', 'fa-cash-register', 'green'];
if (has_permission('finance.reports.view')) $quickLinks[] = ['Finance reports', 'Review collections and balances', 'finance/reports', 'fa-chart-column', 'indigo'];
if (has_permission('payroll.manage')) $quickLinks[] = ['Payroll', 'Prepare and review payroll', 'payroll', 'fa-file-invoice-dollar', 'pink'];
if (has_permission('academic.subjects.manage')) $quickLinks[] = ['Subjects', 'Manage subjects and assignments', 'academic/subjects', 'fa-book-open', 'cyan'];
$quickLinks[] = [has_permission('dashboard.events.manage') ? 'Manage events' : 'School events', 'View school activities', 'events', 'fa-calendar-days', 'yellow'];
if (has_permission('administration.settings.manage')) $quickLinks[] = ['Settings', 'School-wide configuration', 'admin/settings', 'fa-gear', 'slate'];
?>

<section class="dashboard-hero mb-4">
    <div class="dashboard-hero-copy">
        <span class="dashboard-eyebrow"><i class="fas fa-sparkles me-2" aria-hidden="true"></i>School overview</span>
        <h1>Welcome back, <?= html_escape($current_user->first_name ?? 'Administrator'); ?></h1>
        <p>Your school at a glance. Choose a school year to review enrollment.</p>
    </div>
    <form method="get" action="<?= site_url('dashboard'); ?>" class="dashboard-year-filter">
        <label for="dashboard-school-year">Enrollment school year</label>
        <div class="d-flex gap-2">
            <select class="form-select" name="school_year" id="dashboard-school-year" <?= empty($school_years) ? 'disabled' : ''; ?>>
                <?php if (empty($school_years)): ?><option value="">No active years configured</option><?php else: foreach ($school_years as $year): ?>
                    <option value="<?= html_escape($year->school_year); ?>" <?= (string) $year->school_year === (string) $selected_school_year ? 'selected' : ''; ?>><?= html_escape($year->school_year); ?></option>
                <?php endforeach; endif; ?>
            </select>
            <?php if (!empty($school_years)): ?><button class="btn btn-light" type="submit">View</button><?php endif; ?>
        </div>
    </form>
</section>

<?php $this->load->view('dashboard/widgets/statistics_chart'); ?>

<div class="row g-4">
    <div class="col-xl-7">
        <section class="card dashboard-panel h-100">
            <div class="dashboard-panel-heading">
                <div class="dashboard-panel-icon dashboard-panel-icon-blue"><i class="fas fa-chart-bar" aria-hidden="true"></i></div>
                <div><h2>Enrollment by grade</h2><p>Active students in <?= !empty($selected_school_year) ? html_escape($selected_school_year) : 'the selected year'; ?></p></div>
            </div>
            <?php if (empty($students_by_grade)): ?>
                <div class="dashboard-empty-state"><i class="fas fa-chart-simple" aria-hidden="true"></i><span>No active enrollments for this school year yet.</span></div>
            <?php else: ?>
                <div class="grade-breakdown-list">
                    <?php foreach ($students_by_grade as $grade): $gradeCount = (int) $grade->student_count; $gradePercent = (int) ($active_student_count ?? 0) > 0 ? round(($gradeCount / (int) $active_student_count) * 100) : 0; ?>
                        <div class="grade-breakdown-item">
                            <div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-semibold"><?= html_escape($grade->grade); ?></span><span class="text-secondary small"><?= number_format($gradeCount); ?> students <span class="ms-1">(<?= $gradePercent; ?>%)</span></span></div>
                            <div class="progress" role="progressbar" aria-label="<?= html_escape($grade->grade); ?> enrollment" aria-valuenow="<?= $gradePercent; ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: <?= $gradePercent; ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <div class="col-xl-5">
        <section class="card dashboard-panel h-100">
            <div class="dashboard-panel-heading">
                <div class="dashboard-panel-icon dashboard-panel-icon-purple"><i class="fas fa-bolt" aria-hidden="true"></i></div>
                <div><h2>Quick actions</h2><p>Shortcuts based on your access.</p></div>
            </div>
            <?php if (empty($quickLinks)): ?>
                <div class="dashboard-empty-state"><span>No shortcuts are available for this account.</span></div>
            <?php else: ?>
                <div class="dashboard-quick-links">
                    <?php foreach ($quickLinks as $link): ?>
                        <a class="dashboard-quick-link" href="<?= site_url($link[2]); ?>">
                            <span class="quick-link-icon quick-link-<?= html_escape($link[4]); ?>"><i class="fas <?= html_escape($link[3]); ?>" aria-hidden="true"></i></span>
                            <span class="quick-link-copy"><strong><?= html_escape($link[0]); ?></strong><small><?= html_escape($link[1]); ?></small></span>
                            <i class="fas fa-arrow-up-right-from-square quick-link-arrow" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="mt-4">
    <?php $this->load->view('dashboard/widgets/events_preview'); ?>
</div>
