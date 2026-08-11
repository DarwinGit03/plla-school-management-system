<?php

$currentController = strtolower($this->router->fetch_class());

?>

<ul class="nav flex-column">

    <!-- Dashboard -->

    <li class="nav-item">

        <a
            href="<?= site_url('dashboard'); ?>"
            class="nav-link <?= ($currentController == 'dashboard') ? 'active' : ''; ?>">

            <i class="fas fa-gauge-high me-2"></i>

            Dashboard

        </a>

    </li>

    <!-- Academic Management -->

    <li class="nav-item">

        <a
            class="nav-link text-white"
            data-bs-toggle="collapse"
            href="#academicMenu"
            role="button">

            <i class="fas fa-school me-2"></i>

            Academic

            <i class="fas fa-chevron-down float-end mt-1"></i>

        </a>

        <div
            class="collapse"
            id="academicMenu">

            <ul class="nav flex-column ms-3">

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Programs

                    </a>

                </li>

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Subjects

                    </a>

                </li>

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Sections

                    </a>

                </li>

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Rooms

                    </a>

                </li>

            </ul>

        </div>

    </li>

    <!-- Student Management -->

    <li class="nav-item">

        <a
            class="nav-link text-white"
            data-bs-toggle="collapse"
            href="#studentMenu">

            <i class="fas fa-user-graduate me-2"></i>

            Students

            <i class="fas fa-chevron-down float-end mt-1"></i>

        </a>

        <div
            class="collapse"
            id="studentMenu">

            <ul class="nav flex-column ms-3">

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Student List

                    </a>

                </li>

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Enrollment

                    </a>

                </li>

                <li>

                    <a
                        href="#"
                        class="nav-link">

                        Alumni

                    </a>

                </li>

            </ul>

        </div>

    </li>

    <!-- Faculty -->

    <li class="nav-item">

        <a
            class="nav-link text-white"
            data-bs-toggle="collapse"
            href="#facultyMenu">

            <i class="fas fa-chalkboard-teacher me-2"></i>

            Faculty

            <i class="fas fa-chevron-down float-end mt-1"></i>

        </a>

        <div
            class="collapse"
            id="facultyMenu">

            <ul class="nav flex-column ms-3">

                <li>

                    <a href="#" class="nav-link">

                        Faculty List

                    </a>

                </li>

                <li>

                    <a href="#" class="nav-link">

                        Departments

                    </a>

                </li>

                <li>

                    <a href="#" class="nav-link">

                        Teaching Load

                    </a>

                </li>

            </ul>

        </div>

    </li>

    <!-- Finance -->

    <li class="nav-item">

        <a href="#" class="nav-link text-white">

            <i class="fas fa-coins me-2"></i>

            Finance

        </a>

    </li>

    <!-- Reports -->

    <li class="nav-item">

        <a href="#" class="nav-link text-white">

            <i class="fas fa-chart-line me-2"></i>

            Reports

        </a>

    </li>

    <!-- Administration -->

    <li class="nav-item">

        <a
            class="nav-link text-white"
            data-bs-toggle="collapse"
            href="#adminMenu">

            <i class="fas fa-user-shield me-2"></i>

            Administration

            <i class="fas fa-chevron-down float-end mt-1"></i>

        </a>

        <div
            class="collapse"
            id="adminMenu">

            <ul class="nav flex-column ms-3">

                <li>

                    <a href="#" class="nav-link">

                        Users

                    </a>

                </li>

                <li>

                    <a href="#" class="nav-link">

                        Roles

                    </a>

                </li>

                <li>

                    <a href="#" class="nav-link">

                        Audit Logs

                    </a>

                </li>

                <li>

                    <a href="#" class="nav-link">

                        Settings

                    </a>

                </li>

            </ul>

        </div>

    </li>

</ul>