<div class="row g-3 mb-4">
    <div class="col-md-6">
        <article class="dashboard-stat-card dashboard-stat-students h-100">
            <div class="dashboard-stat-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
            <div><div class="dashboard-stat-label">Active student enrollments</div><div class="dashboard-stat-value"><?= number_format((int) ($active_student_count ?? 0)); ?></div><div class="dashboard-stat-note"><?= !empty($selected_school_year) ? 'School year ' . html_escape($selected_school_year) : 'No active school year selected'; ?></div></div>
            <i class="fas fa-user-graduate dashboard-stat-watermark" aria-hidden="true"></i>
        </article>
    </div>
    <div class="col-md-6">
        <article class="dashboard-stat-card dashboard-stat-employees h-100">
            <div class="dashboard-stat-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
            <div><div class="dashboard-stat-label">Active employees</div><div class="dashboard-stat-value"><?= number_format((int) ($active_employee_count ?? 0)); ?></div><div class="dashboard-stat-note">Faculty and staff</div></div>
            <i class="fas fa-users dashboard-stat-watermark" aria-hidden="true"></i>
        </article>
    </div>
</div>
