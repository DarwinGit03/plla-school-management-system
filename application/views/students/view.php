<div class="container-fluid">

    <!-- =====================================================
         Header
    ====================================================== -->

    <div
        class="d-flex
               flex-column
               flex-lg-row
               justify-content-between
               align-items-lg-center
               gap-3
               mb-4">

        <div>

            <div class="mb-2">

                <a
                    href="<?= site_url('students'); ?>"
                    class="text-decoration-none">

                    <i class="fas fa-arrow-left me-1"></i>

                    Back to Students

                </a>

            </div>

            <h1 class="h3 fw-bold mb-1">

                Student Profile

            </h1>

            <p class="text-muted mb-0">

                View complete student information.

            </p>

        </div>


        <div class="d-flex flex-wrap gap-2">

            <a
                href="<?= site_url(
                    'students/edit/' . $student->id
                ); ?>"
                class="btn btn-primary">

                <i class="fas fa-edit me-1"></i>

                Edit Student

            </a>

        </div>

    </div>


    <!-- =====================================================
         Student Summary
    ====================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div
                class="d-flex
                       flex-column
                       flex-md-row
                       align-items-md-center
                       gap-3">

                <!-- Avatar -->

                <div
                    class="rounded-circle
                           bg-primary
                           text-white
                           d-flex
                           align-items-center
                           justify-content-center
                           flex-shrink-0"
                    style="width:72px;height:72px;">

                    <span class="fs-3 fw-bold">

                        <?= strtoupper(
                            substr(
                                $student->first_name,
                                0,
                                1
                            )
                        ); ?>

                        <?= strtoupper(
                            substr(
                                $student->last_name,
                                0,
                                1
                            )
                        ); ?>

                    </span>

                </div>


                <!-- Student Name -->

                <div class="flex-grow-1">

                    <h2 class="h4 fw-bold mb-1">

                        <?= html_escape(
                            $student->first_name
                        ); ?>

                        <?php if (!empty($student->middle_name)): ?>

                            <?= html_escape(
                                ' ' .
                                $student->middle_name
                            ); ?>

                        <?php endif; ?>

                        <?= html_escape(
                            ' ' .
                            $student->last_name
                        ); ?>

                        <?php if (!empty($student->suffix)): ?>

                            <?= html_escape(
                                ' ' .
                                $student->suffix
                            ); ?>

                        <?php endif; ?>

                    </h2>


                    <div class="text-muted small">

                        Student No:

                        <strong>

                            <?= html_escape(
                                $student->student_no
                            ); ?>

                        </strong>

                        <?php if (!empty($student->lrn)): ?>

                            <span class="mx-2">•</span>

                            LRN:

                            <strong>

                                <?= html_escape(
                                    $student->lrn
                                ); ?>

                            </strong>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- Status -->

                <div>

                    <?php if ($student->status === 'active'): ?>

                        <span class="badge text-bg-success px-3 py-2">

                            <i class="fas fa-circle me-1"></i>

                            Active

                        </span>

                    <?php elseif ($student->status === 'pending'): ?>

                        <span class="badge text-bg-warning px-3 py-2">

                            Pending

                        </span>

                    <?php else: ?>

                        <span class="badge text-bg-secondary px-3 py-2">

                            <?= ucfirst(
                                html_escape(
                                    $student->status
                                )
                            ); ?>

                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         Navigation Tabs
    ====================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-0">

            <ul
                class="nav nav-tabs px-3 pt-3"
                role="tablist">

                <li class="nav-item">

                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#personal"
                        type="button">

                        <i class="fas fa-user me-1"></i>

                        Personal

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#guardian"
                        type="button">

                        <i class="fas fa-users me-1"></i>

                        Guardian

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#academic"
                        type="button">

                        <i class="fas fa-graduation-cap me-1"></i>

                        Academic

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#medical"
                        type="button">

                        <i class="fas fa-heartbeat me-1"></i>

                        Medical

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#documents"
                        type="button">

                        <i class="fas fa-file-alt me-1"></i>

                        Documents

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#enrollment"
                        type="button">

                        <i class="fas fa-history me-1"></i>

                        Enrollment History

                    </button>

                </li>

            </ul>

        </div>

    </div>


    <!-- =====================================================
         Tab Content
    ====================================================== -->

    <div class="tab-content">


        <!-- =================================================
             Personal
        ================================================== -->

            
        <div
            class="tab-pane fade show active"
            id="personal">

            <!-- Personal section -->
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">

                        Personal Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                First Name

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->first_name
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Middle Name

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->middle_name
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Last Name

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->last_name
                                ); ?>

                            </div>

                        </div>


                        <!-- <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Suffix

                            </div>

                            <div class="fw-semibold">

                                <comment?= html_escape(
                                    $student->suffix
                                    ?: '—'
                                ); ?>

                            </div>

                        </div> -->


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Gender

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->gender
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Birth Date

                            </div>

                            <div class="fw-semibold">

                                <?php if (!empty($student->birth_date)): ?>

                                    <?= date(
                                        'F d, Y',
                                        strtotime(
                                            $student->birth_date
                                        )
                                    ); ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Age

                            </div>

                            <div class="fw-semibold">

                                <?php if (!empty($student->birth_date)): ?>

                                    <?php

                                    $birth_date =
                                        new DateTime(
                                            $student->birth_date
                                        );

                                    $today =
                                        new DateTime();

                                    $age =
                                        $birth_date->diff(
                                            $today
                                        );

                                    ?>

                                    <?= $age->y; ?>
                                    <?= $age->y === 1 ? 'yr' : 'yrs'; ?>
                                    old

                                    <?php if ($age->d > 0): ?>

                                        &

                                        <?= $age->d; ?>

                                        <?= $age->d === 1 ? 'day' : 'days'; ?>

                                    <?php endif; ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Birth Place

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->birth_place
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Nationality

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->nationality
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
            
            <!-- Contact section -->
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">

                        Contact Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Contact Number

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->mobile_no
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>

                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Email Address

                            </div>

                            <div class="fw-semibold">

                                <?php if (!empty($student->email)): ?>

                                    <a
                                        href="mailto:<?= html_escape($student->email); ?>"
                                        class="text-decoration-none">

                                        <?= html_escape(
                                            $student->email
                                        ); ?>

                                    </a>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>
                    
                </div>
                
            </div>

            <!-- Address section -->
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">

                        Address

                    </h5>

                </div>


                <div class="card-body">
                    <div class="row g-4">
                        
                        <?php if (!empty($addresses)): ?>

                            <?php foreach ($addresses as $address): ?>

                                <div class="col-12 col-lg-6">

                                    <div class="card border-0 shadow-sm h-100">

                                        <div class=" bg-white">

                                            <div class="small text-muted mb-1">

                                                <?= ucfirst(
                                                    html_escape(
                                                        $address->address_type
                                                    )
                                                ); ?>

                                            </div>
                                        </div>

                                    <div class="card-body">

                                        <?php

                                            $address_parts = array_filter([

                                                $address->house_no,

                                                $address->street,

                                                $address->barangay,

                                                $address->city,

                                                $address->province,

                                                $address->postal_code

                                            ]);

                                            ?>

                                        <?php if (!empty($address_parts)): ?>
                                            
                                            <div class="fw-semibold">
                                                <?= html_escape(
                                                    implode(
                                                            ', ',
                                                            $address_parts
                                                        )
                                                ); ?>

                                            </div>

                                        <?php else: ?>
                                            <span class="text-muted">
                                                No address information.

                                            </span>

                                        <?php endif; ?>

                                    </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="col-12">

                                <div class="card border-0 shadow-sm">

                                    <div
                                        class="card-body
                                            text-center
                                            py-5">

                                        <i
                                            class="fas
                                                fa-location-dot
                                                fa-2x
                                                text-muted
                                                mb-3">
                                        </i>

                                        <h5 class="fw-bold">

                                            No Address Information

                                        </h5>

                                        <p class="text-muted mb-0">

                                            Address information has not
                                            been added yet.

                                        </p>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>
                    
                </div>
                
            </div>


        </div>

        <!-- =================================================
             Guardian
        ================================================== -->

        <div
            class="tab-pane fade"
            id="guardian">

            <div class="row g-4">

                <?php if (!empty($guardians)): ?>

                    <?php foreach ($guardians as $guardian): ?>

                        <div class="col-12 col-lg-6">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-header bg-white">

                                    <div
                                        class="d-flex
                                            justify-content-between
                                            align-items-center">

                                        <h5 class="fw-bold mb-0">

                                            <?= ucfirst(
                                                html_escape(
                                                    $guardian->guardian_type
                                                )
                                            ); ?>

                                        </h5>

                                        <?php if (
                                            $guardian->is_primary
                                        ): ?>

                                            <span
                                                class="badge text-bg-primary">

                                                Primary

                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <div class="card-body">

                                    <h5 class="fw-semibold">

                                        <?= html_escape(
                                            $guardian->first_name
                                        ); ?>

                                        <?= html_escape(
                                            $guardian->middle_name
                                            ? ' ' .
                                            $guardian->middle_name
                                            : ''
                                        ); ?>

                                        <?= html_escape(
                                            ' ' .
                                            $guardian->last_name
                                        ); ?>

                                    </h5>


                                    <?php if (
                                        !empty(
                                            $guardian->relationship
                                        )
                                    ): ?>

                                        <p class="text-muted mb-3">

                                            <?= html_escape(
                                                $guardian->relationship
                                            ); ?>

                                        </p>

                                    <?php endif; ?>


                                    <div class="small">

                                        <?php if (
                                            !empty(
                                                $guardian->mobile_no
                                            )
                                        ): ?>

                                            <div class="mb-2">

                                                <i
                                                    class="fas
                                                        fa-mobile-screen
                                                        me-2
                                                        text-muted">
                                                </i>

                                                <?= html_escape(
                                                    $guardian->mobile_no
                                                ); ?>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty(
                                                $guardian->email
                                            )
                                        ): ?>

                                            <div class="mb-2">

                                                <i
                                                    class="fas
                                                        fa-envelope
                                                        me-2
                                                        text-muted">
                                                </i>

                                                <?= html_escape(
                                                    $guardian->email
                                                ); ?>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty(
                                                $guardian->occupation
                                            )
                                        ): ?>

                                            <div>

                                                <i
                                                    class="fas
                                                        fa-briefcase
                                                        me-2
                                                        text-muted">
                                                </i>

                                                <?= html_escape(
                                                    $guardian->occupation
                                                ); ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="col-12">

                        <div class="card border-0 shadow-sm">

                            <div
                                class="card-body
                                    text-center
                                    py-5">

                                <i
                                    class="fas
                                        fa-users
                                        fa-2x
                                        text-muted
                                        mb-3">
                                </i>

                                <h5 class="fw-bold">

                                    No Guardian Information

                                </h5>

                                <p class="text-muted mb-0">

                                    Guardian information has not
                                    been added yet.

                                </p>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- =================================================
             Academic
        ================================================== -->

        <div
            class="tab-pane fade"
            id="academic">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">

                        Academic Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Academic Year

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->academic_year
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Grade Level

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->grade_level
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>


                        <!-- <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Program

                            </div>

                            <div class="fw-semibold">

                                < ?= html_escape(
                                    $student->program
                                    ?: '—'
                                ); ?>

                            </div>

                        </div> -->


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Section

                            </div>

                            <div class="fw-semibold">

                                <?= html_escape(
                                    $student->section
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Admission Type

                            </div>

                            <div class="fw-semibold text-capitalize">

                                <?= html_escape(
                                    $student->admission_type
                                    ?: '—'
                                ); ?>

                            </div>

                        </div>


                        <div class="col-12 col-md-6 col-lg-4">

                            <div class="small text-muted mb-1">

                                Enrollment Status

                            </div>

                            <div>

                                <?php if (
                                    $student->enrollment_status === 'active'
                                ): ?>

                                    <span
                                        class="badge text-bg-success">

                                        Active

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="badge text-bg-secondary">

                                        <?= html_escape(
                                            $student->enrollment_status
                                            ?: '—'
                                        ); ?>

                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Medical
        ================================================== -->

        <div
            class="tab-pane fade"
            id="medical">

            <div class="card border-0 shadow-sm">

                <div class="card-body py-5 text-center">

                    <i
                        class="fas fa-heartbeat
                               fa-2x
                               text-muted
                               mb-3">
                    </i>

                    <h5 class="fw-bold">

                        Medical Information

                    </h5>

                    <p class="text-muted mb-0">

                        Medical information will be added
                        in the next phase.

                    </p>

                </div>

            </div>

        </div>


        <!-- =================================================
             Documents
        ================================================== -->

        <div
            class="tab-pane fade"
            id="documents">

            <div class="card border-0 shadow-sm">

                <div class="card-body py-5 text-center">

                    <i
                        class="fas fa-folder-open
                               fa-2x
                               text-muted
                               mb-3">
                    </i>

                    <h5 class="fw-bold">

                        Student Documents

                    </h5>

                    <p class="text-muted mb-0">

                        Document management will be added
                        in the next phase.

                    </p>

                </div>

            </div>

        </div>


        <!-- =================================================
             Enrollment History
        ================================================== -->

        <div
            class="tab-pane fade"
            id="enrollment">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="table-responsive">

                        <table
                            class="table
                                   table-hover
                                   align-middle
                                   mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>
                                        Academic Year
                                    </th>

                                    <th>
                                        Grade
                                    </th>

                                    <!-- <th>
                                        Program
                                    </th> -->

                                    <th>
                                        Section
                                    </th>

                                    <th>
                                        Admission
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php if (!empty($enrollment_history)): ?>

                                <?php foreach (
                                    $enrollment_history
                                    as $enrollment
                                ): ?>

                                    <tr>

                                        <td>

                                            <?= html_escape(
                                                $enrollment->academic_year
                                                ?: '—'
                                            ); ?>

                                        </td>


                                        <td>

                                            <?= html_escape(
                                                $enrollment->grade_level
                                                ?: '—'
                                            ); ?>

                                        </td>


                                        <td>

                                            <?= html_escape(
                                                $enrollment->section
                                                ?: '—'
                                            ); ?>

                                        </td>


                                        <td class="text-capitalize">

                                            <?= html_escape(
                                                $enrollment->admission_type
                                                ?: '—'
                                            ); ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                $enrollment->status === 'active'
                                            ): ?>

                                                <span
                                                    class="badge text-bg-success">

                                                    Active

                                                </span>

                                            <?php elseif (
                                                $enrollment->status === 'completed'
                                            ): ?>

                                                <span
                                                    class="badge text-bg-secondary">

                                                    Completed

                                                </span>

                                            <?php elseif (
                                                $enrollment->status === 'dropped'
                                            ): ?>

                                                <span
                                                    class="badge text-bg-danger">

                                                    Dropped

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge text-bg-secondary">

                                                    <?= html_escape(
                                                        ucfirst(
                                                            $enrollment->status
                                                        )
                                                    ); ?>

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-4 text-muted">

                                        No enrollment history found.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>