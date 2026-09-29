<?php

$currentController =
    strtolower(
        $this->router->fetch_class()
    );

$currentMethod =
    strtolower(
        $this->router->fetch_method()
    );


/*
|--------------------------------------------------------------------------
| Active Menu Detection
|--------------------------------------------------------------------------
*/

$isDashboard =
    ($currentController === 'dashboard');


$isStudents =
    ($currentController === 'students');

$isFinance =
    ($currentController === 'finance');

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
            href="#academicMenu"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="false"
            aria-controls="academicMenu">

            <span>

                <i class="fas fa-school me-2"></i>

                Academic

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="academicMenu">

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


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Sections

                    </a>

                </li>


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
            href="#studentMenu"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= $isStudents ? 'true' : 'false'; ?>"
            aria-controls="studentMenu">

            <span>

                <i class="fas fa-user-graduate me-2"></i>

                Students

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse
                <?= $isStudents ? 'show' : ''; ?>"
            id="studentMenu">

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


    <!-- =====================================================
         FACULTY
    ====================================================== -->

    <li class="nav-item">

        <a
            href="#facultyMenu"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="false"
            aria-controls="facultyMenu">

            <span>

                <i class="fas fa-chalkboard-teacher me-2"></i>

                Faculty

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="facultyMenu">

            <ul class="nav flex-column ms-3">

                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Faculty List

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Departments

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Teaching Load

                    </a>

                </li>

            </ul>

        </div>

    </li>


    <!-- =====================================================
         FINANCE
    ====================================================== -->

    <li class="nav-item">
        <a
            href="#financeMenu"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="<?= $isFinance ? 'true' : 'false'; ?>"
            aria-controls="financeMenu">
            <span><i class="fas fa-coins me-2"></i>Finance</span>
            <i class="fas fa-chevron-down small"></i>
        </a>
        <div class="collapse <?= $isFinance ? 'show' : ''; ?>" id="financeMenu">
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


    <!-- =====================================================
         ADMINISTRATION
    ====================================================== -->

    <li class="nav-item">

        <a
            href="#adminMenu"
            class="nav-link text-white d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="false"
            aria-controls="adminMenu">

            <span>

                <i class="fas fa-user-shield me-2"></i>

                Administration

            </span>

            <i class="fas fa-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="adminMenu">

            <ul class="nav flex-column ms-3">

                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Users

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Roles

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Audit Logs

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="#"
                        class="nav-link text-secondary">

                        Settings

                    </a>

                </li>

            </ul>

        </div>

    </li>

</ul>
