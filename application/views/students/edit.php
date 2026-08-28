<?php
    // $is_admin = $this->session->userdata('role_id') === '3' || $this->session->userdata('role_id') === '2';
    
    $role_id = (int) $this->session->userdata('role_id');
    $is_admin = in_array($role_id, [1, 2], true);
?>

<div class="container-fluid">

    <!-- =====================================================
         Header
    ====================================================== -->

    <div class="d-flex
                flex-column
                flex-lg-row
                justify-content-between
                align-items-lg-center
                gap-3
                mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">
                Edit Student
            </h1>

            <p class="text-muted mb-0">
                Update student information.
            </p>

        </div>

        <a
            href="<?= site_url('students/view/' . $student->id); ?>"
            class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Back to Profile

        </a>

    </div>


    <!-- =====================================================
         Validation Errors
    ====================================================== -->

    <?php if (!empty($validation_errors)): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert">

            <div class="fw-semibold mb-2">
                Please correct the following errors:
            </div>

            <ul class="mb-0">

                <?php foreach ($validation_errors as $section => $errors): ?>

                    <?php if (is_string($errors)): ?>

                        <li>
                            <?= html_escape($errors); ?>
                        </li>

                    <?php elseif (is_array($errors)): ?>

                        <?php foreach ($errors as $guardianIndex => $guardianErrors): ?>

                            <?php if ($guardianIndex === '_global'): ?>

                                <?php if (is_array($guardianErrors)): ?>

                                    <?php foreach ($guardianErrors as $message): ?>

                                        <li>
                                            <?= html_escape($message); ?>
                                        </li>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            <?php elseif (is_array($guardianErrors)): ?>

                                <?php foreach ($guardianErrors as $field => $message): ?>

                                    <li>
                                        Guardian
                                        <?= ((int) $guardianIndex + 1); ?>:
                                        <?= html_escape($message); ?>
                                    </li>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <li>
                                    <?= html_escape($guardianErrors); ?>
                                </li>

                            <?php endif; ?>

                        <?php endforeach; ?>

                    <?php endif; ?>

                <?php endforeach; ?>

            </ul>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>

        </div>

    <?php endif; ?>


    <?php if (!empty($database_error)): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert">

            <?= html_escape($database_error); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         Edit Form
    ====================================================== -->

    <form
        method="post"
        action="<?= site_url('students/edit/' . $student->id); ?>">

        <input
            type="hidden"
            name="<?= $this->security->get_csrf_token_name(); ?>"
            value="<?= $this->security->get_csrf_hash(); ?>">


        <!-- =================================================
             Student Information
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-user text-primary me-2"></i>

                    Student Information

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <!-- LRN -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="lrn"
                            class="form-label">

                            LRN
                            <span class="text-danger">*</span>

                        </label>
                        <input
                            type="text"
                            name="lrn"
                            id="lrn"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'lrn',
                                    $student->lrn ?? ''
                                )
                            ); ?>"
                            <?= !$is_admin ? 'readonly' : '' ?>
                            autocomplete="off"
                            inputmode="numeric"
                            maxlength="20"
                            required>

                            <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="student_no_same_as_lrn"
                                name="student_no_same_as_lrn"
                                <?= !$is_admin ? 'disabled' : '' ?>
                                value="1"
                                <?= set_checkbox(
                                    'student_no_same_as_lrn',
                                    '1'
                                ); ?>
                                >

                            <label
                                class="form-check-label">
                                <!-- for="student_no_same_as_lrn"> -->

                                Student No. is the same as LRN

                            </label>

                        </div>

                    </div>


                    <!-- Student Number -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="student_no"
                            class="form-label">

                            Student No.
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="student_no"
                            id="student_no"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'student_no',
                                    $student->student_no ?? ''
                                )
                            ); ?>"
                            <?= !$is_admin ? 'readonly' : '' ?>
                            autocomplete="off"
                            inputmode="numeric"
                            maxlength="20"
                            >

                    </div>


                    <div class="col-12 col-md-6 col-lg-4">
                        <?php if (!empty($student)): ?>

                        <input
                            type="hidden"
                            name="student_id"
                            value="<?= (int) $student->id ?>"
                        >

                        <?php endif; ?>
                    </div>


                    <!-- First Name -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="first_name"
                            class="form-label">

                            First Name
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'first_name',
                                    $student->first_name ?? ''
                                )
                            ); ?>"
                            required>

                    </div>


                    <!-- Middle Name -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="middle_name"
                            class="form-label">

                            Middle Name / M.I.

                        </label>

                        <input
                            type="text"
                            id="middle_name"
                            name="middle_name"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'middle_name',
                                    $student->middle_name ?? ''
                                )
                            ); ?>">

                    </div>


                    <!-- Last Name -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="last_name"
                            class="form-label">

                            Last Name
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'last_name',
                                    $student->last_name ?? ''
                                )
                            ); ?>"
                            required>

                    </div>

                    <!-- Gender -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="gender"
                            class="form-label">

                            Gender
                            <span class="text-danger">*</span>

                        </label>

                        <?php
                        $gender =
                            set_value(
                                'gender',
                                $student->gender ?? ''
                            );
                        ?>

                        <select
                            id="gender"
                            name="gender"
                            class="form-select"
                            required>

                            <option value="">
                                Select Gender
                            </option>

                            <option
                                value="Male"
                                <?= $gender === 'Male'
                                    ? 'selected'
                                    : ''; ?>>

                                Male

                            </option>

                            <option
                                value="Female"
                                <?= $gender === 'Female'
                                    ? 'selected'
                                    : ''; ?>>

                                Female

                            </option>

                        </select>

                    </div>


                    <!-- Birth Date -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="birth_date"
                            class="form-label">

                            Birth Date
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="date"
                            id="birth_date"
                            name="birth_date"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'birth_date',
                                    $student->birth_date ?? ''
                                )
                            ); ?>"
                            required>

                    </div>


                    <!-- Birth Place -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="birth_place"
                            class="form-label">

                            Birth Place

                        </label>

                        <input
                            type="text"
                            id="birth_place"
                            name="birth_place"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'birth_place',
                                    $student->birth_place ?? ''
                                )
                            ); ?>">

                    </div>


                    <!-- Nationality -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="nationality"
                            class="form-label">

                            Nationality

                        </label>

                        <input
                            type="text"
                            id="nationality"
                            name="nationality"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'nationality',
                                    $student->nationality ?? 'Filipino'
                                )
                            ); ?>">

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Contact Information
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-address-book text-primary me-2"></i>

                    Contact Information

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">

                    <div class="col-12 col-md-6">

                        <label
                            for="mobile_no"
                            class="form-label">

                            Mobile Number

                        </label>

                        <input
                            type="tel"
                            id="mobile_no"
                            name="mobile_no"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'mobile_no',
                                    $student->mobile_no ?? ''
                                )
                            ); ?>"
                            placeholder="09XXXXXXXXX">

                    </div>


                    <div class="col-12 col-md-6">

                        <label
                            for="email"
                            class="form-label">

                            Email Address

                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'email',
                                    $student->email ?? ''
                                )
                            ); ?>">

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Guardian Information
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">

                            <i class="fas fa-hands-holding-child text-primary me-2"></i>

                            Guardian Information

                        </h5>

                        <small class="text-muted">
                            Add the student's parent or
                            <b>legal guardian.</b>
                        </small>

                    </div>


                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        id="addGuardianBtn">

                        <i class="fas fa-plus me-1"></i>

                        Add Guardian

                    </button>

                </div>

            </div>


            <div class="card-body">

                <div
                    id="guardianContainer"
                    class="row g-3">

                    <!-- Guardians generated by JavaScript -->

                </div>

            </div>

        </div>


        <!-- =================================================
             Current Address
        ================================================== -->

        <?php

        $currentAddress = null;
        $permanentAddress = null;

        foreach ($addresses ?? [] as $address) {

            if (
                isset($address->address_type) &&
                $address->address_type === 'current'
            ) {

                $currentAddress = $address;
            }

            if (
                isset($address->address_type) &&
                $address->address_type === 'permanent'
            ) {

                $permanentAddress = $address;
            }
        }

        ?>


        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-1">

                    <i class="fas fa-house text-primary me-2"></i>

                    Current Address

                </h5>

                <small class="text-muted">
                    Student's current residential address
                </small>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <div class="col-12 col-md-4">

                        <label
                            for="current_house_no"
                            class="form-label">

                            House / Building No.

                        </label>

                        <input
                            type="text"
                            name="current_house_no"
                            id="current_house_no"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'current_house_no',
                                    $currentAddress->house_no ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="current_street"
                            class="form-label">

                            Street

                        </label>

                        <input
                            type="text"
                            name="current_street"
                            id="current_street"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'current_street',
                                    $currentAddress->street ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="current_barangay"
                            class="form-label">

                            Barangay

                        </label>

                        <input
                            type="text"
                            name="current_barangay"
                            id="current_barangay"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'current_barangay',
                                    $currentAddress->barangay ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="current_city"
                            class="form-label">

                            City / Municipality

                        </label>

                        <input
                            type="text"
                            name="current_city"
                            id="current_city"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'current_city',
                                    $currentAddress->city ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="current_province"
                            class="form-label">

                            Province

                        </label>

                        <input
                            type="text"
                            name="current_province"
                            id="current_province"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'current_province',
                                    $currentAddress->province ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="current_postal_code"
                            class="form-label">

                            Postal Code

                        </label>

                        <input
                            type="text"
                            name="current_postal_code"
                            id="current_postal_code"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'current_postal_code',
                                    $currentAddress->postal_code ?? ''
                                )
                            ); ?>">

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Same Address
        ================================================== -->

        <?php

        $sameAddress =
            (
                $currentAddress &&
                $permanentAddress &&
                ($currentAddress->house_no ?? '') ===
                    ($permanentAddress->house_no ?? '') &&
                ($currentAddress->street ?? '') ===
                    ($permanentAddress->street ?? '') &&
                ($currentAddress->barangay ?? '') ===
                    ($permanentAddress->barangay ?? '') &&
                ($currentAddress->city ?? '') ===
                    ($permanentAddress->city ?? '') &&
                ($currentAddress->province ?? '') ===
                    ($permanentAddress->province ?? '') &&
                ($currentAddress->postal_code ?? '') ===
                    ($permanentAddress->postal_code ?? '')
            );

        $sameAddressValue =
            set_value(
                'permanent_same_as_current',
                $sameAddress ? '1' : ''
            );

        ?>


        <div class="form-check my-3">

            <input
                class="form-check-input"
                type="checkbox"
                id="permanent_same_as_current"
                name="permanent_same_as_current"
                value="1"
                <?= $sameAddressValue === '1'
                    ? 'checked'
                    : ''; ?>>

            <label
                class="form-check-label text-danger"
                for="permanent_same_as_current">

                Permanent address is the same as current address

            </label>

        </div>


        <!-- =================================================
             Permanent Address
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-1">

                    <i class="fas fa-house text-primary me-2"></i>

                    Permanent Address

                </h5>

                <small class="text-muted">
                    Student's permanent residential address
                </small>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <div class="col-12 col-md-4">

                        <label
                            for="permanent_house_no"
                            class="form-label">

                            House / Building No.

                        </label>

                        <input
                            type="text"
                            name="permanent_house_no"
                            id="permanent_house_no"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'permanent_house_no',
                                    $permanentAddress->house_no ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="permanent_street"
                            class="form-label">

                            Street

                        </label>

                        <input
                            type="text"
                            name="permanent_street"
                            id="permanent_street"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'permanent_street',
                                    $permanentAddress->street ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="permanent_barangay"
                            class="form-label">

                            Barangay

                        </label>

                        <input
                            type="text"
                            name="permanent_barangay"
                            id="permanent_barangay"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'permanent_barangay',
                                    $permanentAddress->barangay ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="permanent_city"
                            class="form-label">

                            City / Municipality

                        </label>

                        <input
                            type="text"
                            name="permanent_city"
                            id="permanent_city"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'permanent_city',
                                    $permanentAddress->city ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="permanent_province"
                            class="form-label">

                            Province

                        </label>

                        <input
                            type="text"
                            name="permanent_province"
                            id="permanent_province"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'permanent_province',
                                    $permanentAddress->province ?? ''
                                )
                            ); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label
                            for="permanent_postal_code"
                            class="form-label">

                            Postal Code

                        </label>

                        <input
                            type="text"
                            name="permanent_postal_code"
                            id="permanent_postal_code"
                            class="form-control"
                            value="<?= html_escape(
                                set_value(
                                    'permanent_postal_code',
                                    $permanentAddress->postal_code ?? ''
                                )
                            ); ?>">

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Enrollment
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-graduation-cap text-primary me-2"></i>

                    Enrollment

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <!-- Academic Year -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="academic_year"
                            class="form-label">

                            Academic Year

                        </label>

                        <select
                            id="academic_year"
                            name="academic_year"
                            class="form-select">

                            <option value="">
                                Select Academic Year
                            </option>

                            <?php

                            $selectedYear =
                                set_value(
                                    'academic_year',
                                    $student->academic_year ?? ''
                                );

                            ?>

                            <?php foreach ($academic_years ?? [] as $year): ?>

                                <?php
                                $yearValue =
                                    is_object($year)
                                        ? $year->year
                                        : $year;
                                ?>

                                <option
                                    value="<?= html_escape($yearValue); ?>"
                                    <?= (string) $selectedYear ===
                                        (string) $yearValue
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= html_escape($yearValue); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Grade Level -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="grade_level"
                            class="form-label">

                            Grade Level

                        </label>

                        <select
                            name="grade_level"
                            id="grade_level"
                            class="form-select"
                            <?= empty($selectedYear)
                                ? 'disabled'
                                : ''; ?>>

                            <option value="">
                                Select Grade Level
                            </option>

                            <?php

                            $selectedGrade =
                                set_value(
                                    'grade_level',
                                    $student->grade_level ?? ''
                                );

                            ?>

                            <?php foreach ($grade_levels ?? [] as $grade): ?>

                                <?php
                                $gradeValue =
                                    is_object($grade)
                                        ? $grade->grade
                                        : $grade;
                                ?>

                                <option
                                    value="<?= html_escape($gradeValue); ?>"
                                    <?= (string) $selectedGrade ===
                                        (string) $gradeValue
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= html_escape($gradeValue); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Section -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="section"
                            class="form-label">

                            Section

                        </label>

                        <select
                            name="section"
                            id="section"
                            class="form-select"
                            <?= empty($selectedGrade)
                                ? 'disabled'
                                : ''; ?>>

                            <option value="">
                                Select Section
                            </option>

                            <?php

                            $selectedSection =
                                set_value(
                                    'section',
                                    $student->section ?? ''
                                );

                            ?>

                            <?php foreach ($sections ?? [] as $section): ?>

                                <?php
                                $sectionValue =
                                    is_object($section)
                                        ? $section->section
                                        : $section;
                                ?>

                                <option
                                    value="<?= html_escape($sectionValue); ?>"
                                    <?= (string) $selectedSection ===
                                        (string) $sectionValue
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= html_escape($sectionValue); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Admission Type -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="admission_type"
                            class="form-label">

                            Admission Type

                            <span class="text-danger">*</span>

                        </label>

                        <?php

                        $admissionType =
                            set_value(
                                'admission_type',
                                $student->admission_type ??
                                'New Student'
                            );

                        ?>

                        <select
                            id="admission_type"
                            name="admission_type"
                            class="form-select"
                            required>

                            <option
                                value="New Student"
                                <?= $admissionType === 'New Student'
                                    ? 'selected'
                                    : ''; ?>>

                                New Student

                            </option>

                            <option
                                value="Transferee"
                                <?= $admissionType === 'Transferee'
                                    ? 'selected'
                                    : ''; ?>>

                                Transferee

                            </option>

                            <option
                                value="Returning Student"
                                <?= $admissionType === 'Returning Student'
                                    ? 'selected'
                                    : ''; ?>>

                                Returning Student

                            </option>

                        </select>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Actions
        ================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div
                    class="d-flex
                           flex-column
                           flex-sm-row
                           justify-content-end
                           gap-2">

                    <a
                        href="<?= site_url('students/view/' . $student->id); ?>"
                        class="btn btn-outline-secondary">

                        Cancel

                    </a>

                    <button
                        type="reset"
                        class="btn btn-light">

                        Reset

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </div>

    </form>

    
    <!-- Duplicate LRN Modal -->
    <div
        class="modal fade"
        id="duplicateLrnModal"
        tabindex="-1"
        aria-labelledby="duplicateLrnModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="duplicateLrnModalLabel">

                        LRN Already Registered

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>

                </div>


                <div class="modal-body">

                    <div class="alert alert-warning">

                        The LRN you entered is already registered in the system.

                    </div>


                    <div class="mb-2">

                        <strong>Student:</strong>

                        <span id="duplicateStudentName">
                        </span>

                    </div>


                    <div class="mb-2">

                        <strong>Academic Year:</strong>

                        <span id="duplicateAcademicYear">
                        </span>

                    </div>


                    <div class="mb-2">

                        <strong>Grade Level:</strong>

                        <span id="duplicateGradeLevel">
                        </span>

                    </div>


                    <div class="mb-2">

                        <strong>Section:</strong>

                        <span id="duplicateSection">
                        </span>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Close

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     JavaScript Data
========================================================== -->

<script>

    const submittedGuardians =
        <?= json_encode($guardians ?? []); ?>;

    const classAssignmentMode = 'edit';

    const submittedEnrollment = {

        academic_year:
            <?= json_encode(
                set_value(
                    'academic_year',
                    $student->academic_year ?? ''
                )
            ); ?>,

        grade_level:
            <?= json_encode(
                set_value(
                    'grade_level',
                    $student->grade_level ?? ''
                )
            ); ?>,

        section:
            <?= json_encode(
                set_value(
                    'section',
                    $student->section ?? ''
                )
            ); ?>
    };

    const BASE_URL =
        <?= json_encode(base_url()); ?>;

</script>



<script src="<?= base_url(
    'assets/js/student/lrn_checking.js'
); ?>"></script>
<script src="<?= base_url(
    'assets/js/student/field_validation.js'
); ?>"></script>
<script src="<?= base_url(
    'assets/js/student/class_assignment.js'
); ?>"></script>

<script src="<?= base_url(
    'assets/js/student/general_script.js'
); ?>"></script>
