<?php

$currentController =
    strtolower(
        $this->router->fetch_class()
    );

$currentMethod =
    strtolower(
        $this->router->fetch_method()
    );

$menuSuffix = preg_replace('/[^A-Za-z0-9_-]/', '', $menu_suffix ?? 'Menu');
$academicMenuId = 'academicMenu' . $menuSuffix;
$studentMenuId = 'studentMenu' . $menuSuffix;
$facultyMenuId = 'facultyMenu' . $menuSuffix;
$financeMenuId = 'financeMenu' . $menuSuffix;
$adminMenuId = 'adminMenu' . $menuSuffix;
$isAdministration = ($currentController === 'administration');


/*
|--------------------------------------------------------------------------
| Active Menu Detection
|--------------------------------------------------------------------------
*/

$isDashboard =
    ($currentController === 'dashboard');
$isEvents = ($currentController === 'events');

$isAcademicSections =
    ($currentController === 'sections');
$isAcademicSubjects = ($currentController === 'subjects');
$isMyClasses = ($currentController === 'my_classes');


$isStudents =
    in_array($currentController, ['students', 'section_assignments'], true);

$isSectionAssignments = ($currentController === 'section_assignments');
$isTeachers = ($currentController === 'teachers');
$isPayroll = ($currentController === 'payroll');
$isMyPayroll = ($currentController === 'my_payroll');

$isFinance = ($currentController === 'finance');
$isParentPaymentHistory =
    ($currentController === 'parents' && $currentMethod === 'payment_history');


$isStudentList =
    (
        $currentController === 'students'
        &&
        $currentMethod === 'index'
    );


$registration =
    (
        $currentController === 'students'
        &&
        $currentMethod === 'create'
    );

$isEnrollment =
    (
        $currentController === 'students'
        && in_array($currentMethod, ['enroll', 'enroll_student'], true)
    );

?>

<ul class="nav flex-column">


    <!-- =====================================================
         DASHBOARD
    ====================================================== -->

    <li class="nav-item">

        <a
            href="<?= site_url('dashboard'); ?>"
            class="nav-link text-white
                <?= $isDashboard ? 'active' : ''; ?>">

            <i class="fas fa-gauge-high me-2"></i>

            Dashboard

        </a>

    </li>

    <?php if (has_permission('payroll.self.view')): ?>
        <li class="nav-item">
            <a href="<?= site_url('my-payroll'); ?>" class="nav-link text-white <?= $isMyPayroll ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                My Account and payslips
            </a>
        </li>
    <?php endif; ?>

    <?php if (has_permission('parent.payments.view')): ?>
        <li class="nav-item">
            <a
                href="<?= site_url('parents/payment-history'); ?>"
                class="nav-link text-white <?= $isParentPaymentHistory ? 'active' : ''; ?>">
                <i class="fas fa-receipt me-2"></i>
                Payment History
            </a>
        </li>
    <?php endif; ?>


    <!-- =====================================================
         ACADEMIC
    ====================================================== -->

    <li class="nav-item">

        <a
            href="#<?= $academicMenuId; ?>"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= ($isAcademicSections || $isAcademicSubjects || $isMyClasses || $isEvents) ? 'true' : 'false'; ?>"
            aria-controls="<?= $academicMenuId; ?>">

            <span>

                <i class="fas fa-school me-2"></i>

                Academic

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse <?= ($isAcademicSections || $isAcademicSubjects || $isMyClasses || $isEvents) ? 'show' : ''; ?>"
            id="<?= $academicMenuId; ?>">

            <ul class="nav flex-column ms-3">

                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Programs

                    </a>

                </li>

                <li class="nav-item">
                    <a href="<?= site_url('events'); ?>" class="nav-link text-secondary <?= $isEvents ? 'submenu-active' : ''; ?>">Events</a>
                </li>

                <?php if (has_permission('academic.subjects.manage')): ?>
                <li class="nav-item">
                    <a href="<?= site_url('academic/subjects'); ?>" class="nav-link text-secondary <?= $isAcademicSubjects ? 'submenu-active' : ''; ?>">Subjects and Assignments</a>
                </li>
                <?php endif; ?>

                <?php if (has_permission('academic.subjects.view_own')): ?>
                <li class="nav-item">
                    <a href="<?= site_url('my-classes'); ?>" class="nav-link text-secondary <?= $isMyClasses ? 'submenu-active' : ''; ?>">My classes</a>
                </li>
                <?php endif; ?>


                <?php if (has_permission('academic.sections.manage')): ?>
                <li class="nav-item">

                    <a
                        href="<?= site_url('academic/sections'); ?>"
                        class="nav-link text-secondary <?= $isAcademicSections ? 'submenu-active' : ''; ?>">

                        Sections

                    </a>

                </li>
                <?php endif; ?>


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Rooms

                    </a>

                </li>

            </ul>

        </div>

    </li>


    <!-- =====================================================
         STUDENTS
    ====================================================== -->

    <?php if (has_permission('students.view') || has_permission('students.manage') || has_permission('students.enrollment.manage') || has_permission('students.section.assign')): ?>
    <li class="nav-item">

        <a
            href="#<?= $studentMenuId; ?>"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= $isStudents ? 'true' : 'false'; ?>"
            aria-controls="<?= $studentMenuId; ?>">

            <span>

                <i class="fas fa-user-graduate me-2"></i>

                Students

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse
                <?= $isStudents ? 'show' : ''; ?>"
            id="<?= $studentMenuId; ?>">

            <ul class="nav flex-column ms-3">

                <!-- Student List -->

                <li class="nav-item">

                    <a
                        href="<?= site_url('students'); ?>"
                        class="nav-link text-secondary
                            <?= $isStudentList ? 'submenu-active' : ''; ?>">

                        Student List

                    </a>

                </li>


                <!-- Registration -->

                <li class="nav-item">

                    <a
                        href="<?= site_url('students/create'); ?>"
                        class="nav-link text-secondary
                            <?= $registration ? 'submenu-active' : ''; ?>">

                        Registration

                    </a>

                </li>

                <!-- Enrollment -->

                <li class="nav-item">

                    <a
                        href="<?= site_url('students/enroll'); ?>"
                        class="nav-link text-secondary
                            <?= $isEnrollment ? 'submenu-active' : ''; ?>">

                        Enrollment

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        href="<?= site_url('students/section-assignments'); ?>"
                        class="nav-link text-secondary <?= $isSectionAssignments ? 'submenu-active' : ''; ?>">

                        Section Assignment

                    </a>

                </li>

    <!-- Alumni -->

                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Alumni

                    </a>

                </li>

            </ul>

        </div>

    </li>


    <?php endif; ?>

    <!-- =====================================================
         FACULTY
    ====================================================== -->

    <?php if (has_permission('faculty.view') || has_permission('payroll.manage')): ?>
    <li class="nav-item">

        <a
            href="#<?= $facultyMenuId; ?>"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= ($isTeachers || $isPayroll) ? 'true' : 'false'; ?>"
            aria-controls="<?= $facultyMenuId; ?>">

            <span>

                <i class="fas fa-chalkboard-teacher me-2"></i>

                Faculty

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse <?= ($isTeachers || $isPayroll) ? 'show' : ''; ?>"
            id="<?= $facultyMenuId; ?>">

            <ul class="nav flex-column ms-3">

                <li class="nav-item">

                    <a
                        href="<?= site_url('teachers'); ?>"
                        class="nav-link <?= $isTeachers ? 'active' : 'text-secondary'; ?>">

                        Faculty List

                    </a>

                </li>

                <li class="nav-item">
                    <a href="<?= site_url('payroll'); ?>" class="nav-link <?= $isPayroll ? 'active' : 'text-secondary'; ?>">
                        Payroll
                    </a>
                </li>


            </ul>

        </div>

    </li>

    <?php endif; ?>


    <!-- =====================================================
         FINANCE
    ====================================================== -->

    <?php if (has_permission('finance.fees.manage') || has_permission('finance.payments.manage') || has_permission('finance.reports.view')): ?>
    <li class="nav-item">
        <a
            href="#<?= $financeMenuId; ?>"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= $isFinance ? 'true' : 'false'; ?>"
            aria-controls="<?= $financeMenuId; ?>">
            <span><i class="fas fa-coins me-2"></i>Finance</span>
            <i class="fas fa-chevron-down small"></i>
        </a>
        <div class="collapse <?= $isFinance ? 'show' : ''; ?>" id="<?= $financeMenuId; ?>">
            <ul class="nav flex-column ms-3">
                <?php if (has_permission('finance.fees.manage')): ?>
                <li class="nav-item">
                    <a href="<?= site_url('finance'); ?>" class="nav-link text-secondary <?= $isFinance && $currentMethod === 'index' ? 'submenu-active' : ''; ?>">
                        Fee Configurations
                    </a>
                </li>
                <?php endif; ?>
                <?php if (has_permission('finance.payments.manage')): ?>
                <li class="nav-item">
                    <a href="<?= site_url('finance/payments'); ?>" class="nav-link text-secondary <?= $isFinance && $currentMethod === 'payments' ? 'submenu-active' : ''; ?>">
                        Record Payments
                    </a>
                </li>
                <?php endif; ?>
                <?php if (has_permission('finance.reports.view')): ?>
                <li class="nav-item">
                    <a href="<?= site_url('finance/reports'); ?>" class="nav-link text-secondary <?= $isFinance && $currentMethod === 'reports' ? 'submenu-active' : ''; ?>">
                        Finance Reports
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>
    <?php endif; ?>


    <!-- =====================================================
         REPORTS
    ====================================================== -->

    <?php if (has_permission('administration.users.manage') || has_permission('administration.roles.manage') || has_permission('administration.audit.view') || has_permission('administration.settings.manage')): ?>
    <!-- =====================================================
         ADMINISTRATION
    ====================================================== -->

    <li class="nav-item">

        <a
            href="#<?= $adminMenuId; ?>"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= $isAdministration ? 'true' : 'false'; ?>"
            aria-controls="<?= $adminMenuId; ?>">

            <span>

                <i class="fas fa-user-shield me-2"></i>

                Administration

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse <?= $isAdministration ? 'show' : ''; ?>"
            id="<?= $adminMenuId; ?>">

            <ul class="nav flex-column ms-3">

                <?php if (has_permission('administration.users.manage')): ?><li class="nav-item">

                    <a
                        href="<?= site_url('admin/users'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'users' ? 'submenu-active' : ''; ?>">

                        Users

                    </a>

                </li><?php endif; ?>


                <?php if (has_permission('administration.roles.manage')): ?><li class="nav-item">

                    <a
                        href="<?= site_url('admin/roles'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'roles' ? 'submenu-active' : ''; ?>">

                        Roles

                    </a>

                </li><?php endif; ?>


                <?php if (has_permission('administration.audit.view')): ?><li class="nav-item">

                    <a
                        href="<?= site_url('admin/audit-logs'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'audit_logs' ? 'submenu-active' : ''; ?>">

                        Audit Logs

                    </a>

                </li><?php endif; ?>


                <?php if (has_permission('administration.settings.manage')): ?><li class="nav-item">

                    <a
                        href="<?= site_url('admin/settings'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'settings' ? 'submenu-active' : ''; ?>">

                        Settings

                    </a>

                </li><?php endif; ?>

            </ul>

        </div>

    </li>
    <?php endif; ?>

</ul>
