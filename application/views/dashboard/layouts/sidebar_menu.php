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

$isAcademicSections =
    ($currentController === 'sections');


$isStudents =
    in_array($currentController, ['students', 'section_assignments'], true);

$isSectionAssignments = ($currentController === 'section_assignments');
$isTeachers = ($currentController === 'teachers');
$isPayroll = ($currentController === 'payroll');
$isMyPayroll = ($currentController === 'my_payroll');

$isFinance = ($currentController === 'finance');
$isFinanceStaffPayroll = (int) $this->session->userdata('role_id') === 4
    && in_array($currentController, ['payroll', 'teachers'], true);

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

    <?php if ((int) $this->session->userdata('role_id') === 3): ?>
        <li class="nav-item">
            <a href="<?= site_url('my-payroll'); ?>" class="nav-link text-white <?= $isMyPayroll ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                My pay history
            </a>
        </li>
    <?php endif; ?>

    <?php if ((int) $this->session->userdata('role_id') === 5): ?>
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
            aria-expanded="<?= $isAcademicSections ? 'true' : 'false'; ?>"
            aria-controls="<?= $academicMenuId; ?>">

            <span>

                <i class="fas fa-school me-2"></i>

                Academic

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse <?= $isAcademicSections ? 'show' : ''; ?>"
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

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Subjects

                    </a>

                </li>


                <?php if (in_array((int) $this->session->userdata('role_id'), [1, 2], true)): ?>
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


    <?php if (in_array((int) $this->session->userdata('role_id'), [1, 2], true)): ?>
    <!-- =====================================================
         FACULTY
    ====================================================== -->

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
        <div class="collapse <?= ($isFinance || $isFinanceStaffPayroll) ? 'show' : ''; ?>" id="<?= $financeMenuId; ?>">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="<?= site_url('finance'); ?>" class="nav-link text-secondary <?= $isFinance && $currentMethod === 'index' ? 'submenu-active' : ''; ?>">
                        Fee Configurations
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('finance/payments'); ?>" class="nav-link text-secondary <?= $isFinance && $currentMethod === 'payments' ? 'submenu-active' : ''; ?>">
                        Record Payments
                    </a>
                </li>
                <?php if ((int) $this->session->userdata('role_id') === 4): ?>
                    <li class="nav-item">
                        <a href="<?= site_url('teachers'); ?>" class="nav-link text-secondary <?= $currentController === 'teachers' ? 'submenu-active' : ''; ?>">Faculty salaries</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('payroll'); ?>" class="nav-link text-secondary <?= $isPayroll ? 'submenu-active' : ''; ?>">Teacher payroll</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>


    <!-- =====================================================
         REPORTS
    ====================================================== -->

    <li class="nav-item">

        <a
            href="#"
            class="nav-link text-white">

            <i class="fas fa-chart-line me-2"></i>

            Reports

        </a>

    </li>


    <?php if (in_array((int) $this->session->userdata('role_id'), [1, 2], true)): ?>
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

                <li class="nav-item">

                    <a
                        href="<?= site_url('admin/users'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'users' ? 'submenu-active' : ''; ?>">

                        Users

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="<?= site_url('admin/roles'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'roles' ? 'submenu-active' : ''; ?>">

                        Roles

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="<?= site_url('admin/audit-logs'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'audit_logs' ? 'submenu-active' : ''; ?>">

                        Audit Logs

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="<?= site_url('admin/settings'); ?>"
                        class="nav-link text-secondary <?= $isAdministration && $currentMethod === 'settings' ? 'submenu-active' : ''; ?>">

                        Settings

                    </a>

                </li>

            </ul>

        </div>

    </li>
    <?php endif; ?>

</ul>
