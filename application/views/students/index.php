<div class="container-fluid">

    <!-- =====================================================
         Page Header
    ====================================================== -->

    <div class="d-flex flex-column flex-lg-row
                justify-content-between
                align-items-lg-center
                gap-3
                mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">
                Students
            </h1>

            <p class="text-muted mb-0">
                Manage student information and records.
            </p>

        </div>


        <div class="d-flex flex-wrap gap-2">

            <button
                type="button"
                class="btn btn-outline-secondary">

                <i class="fas fa-file-import me-1"></i>

                Import

            </button>


            <button
                type="button"
                class="btn btn-outline-secondary">

                <i class="fas fa-file-export me-1"></i>

                Export

            </button>


            <a
                href="<?= site_url('students/create'); ?>"
                class="btn btn-primary">

                <i class="fas fa-plus me-1"></i>

                Add Student

            </a>

        </div>

    </div>


    <!-- =====================================================
         Filters
    ====================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="get"
                action="<?= site_url('students'); ?>">

                <div class="row g-3 align-items-end">


                    <!-- Search -->

                    <div class="col-12 col-lg-4">

                        <label class="form-label small fw-semibold">

                            Search

                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">

                                <i class="fas fa-search text-muted"></i>

                            </span>

                            <input
                                type="search"
                                name="search"
                                value="<?= html_escape($filters['search']); ?>"
                                class="form-control"
                                placeholder="Student name, LRN or student no.">

                        </div>

                    </div>


                    <!-- Academic Year -->

                    <div class="col-6 col-lg-2">

                        <label class="form-label small fw-semibold">

                            Academic Year

                        </label>

                        <select
                            name="academic_year"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <option
                                value="2025-2026"
                                <?= $filters['academic_year'] === '2025-2026'
                                    ? 'selected'
                                    : ''; ?>>

                                2025-2026

                            </option>

                            <option
                                value="2024-2025"
                                <?= $filters['academic_year'] === '2024-2025'
                                    ? 'selected'
                                    : ''; ?>>

                                2024-2025

                            </option>

                        </select>

                    </div>


                    <!-- Grade -->

                    <div class="col-6 col-lg-2">

                        <label class="form-label small fw-semibold">

                            Grade Level

                        </label>

                        <select
                            name="grade_level"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <?php for ($i = 1; $i <= 12; $i++): ?>

                                <option
                                    value="Grade <?= $i; ?>"
                                    <?= $filters['grade_level'] === "Grade {$i}"
                                        ? 'selected'
                                        : ''; ?>>

                                    Grade <?= $i; ?>

                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>


                    <!-- Status -->

                    <div class="col-6 col-lg-2">

                        <label class="form-label small fw-semibold">

                            Status

                        </label>

                        <select
                            name="status"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <option
                                value="active"
                                <?= $filters['status'] === 'active'
                                    ? 'selected'
                                    : ''; ?>>

                                Active

                            </option>

                            <option
                                value="pending"
                                <?= $filters['status'] === 'pending'
                                    ? 'selected'
                                    : ''; ?>>

                                Pending

                            </option>

                            <option
                                value="inactive"
                                <?= $filters['status'] === 'inactive'
                                    ? 'selected'
                                    : ''; ?>>

                                Inactive

                            </option>

                        </select>

                    </div>


                    <!-- Filter -->

                    <div class="col-6 col-lg-2">

                        <div class="d-grid">

                            <button
                                type="submit"
                                class="btn btn-primary">

                                <i class="fas fa-filter me-1"></i>

                                Filter

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- =====================================================
         Student Table
    ====================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0 student-table">

                    <thead class="table-light">

                        <tr>

                            <th class="px-3">
                                Student No.
                            </th>

                            <th>
                                LRN
                            </th>

                            <th>
                                Student
                            </th>

                            <th>
                                Grade
                            </th>

                            <th>
                                Section
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-end px-3">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($students)): ?>

                            <?php foreach ($students as $student): ?>

                                <tr>

                                    <td class="px-3 fw-semibold">

                                        <?= html_escape(
                                            $student->student_no
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= html_escape(
                                            $student->lrn ?: '—'
                                        ); ?>

                                    </td>


                                    <td>

                                        <div class="fw-semibold">

                                            <?= html_escape(
                                                $student->last_name
                                            ); ?>,

                                            <?= html_escape(
                                                $student->first_name
                                            ); ?>

                                            <?php if (!empty($student->middle_name)): ?>

                                                <?= html_escape(
                                                    ' ' . strtoupper(
                                                        substr(
                                                            $student->middle_name,
                                                            0,
                                                            1
                                                        )
                                                    ) . '.'
                                                ); ?>

                                            <?php endif; ?>

                                        </div>

                                    </td>


                                    <td>

                                        <?= html_escape(
                                            $student->grade_level ?: '—'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= html_escape(
                                            $student->section ?: '—'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?php

                                        $status_class = 'secondary';

                                        if ($student->status === 'active') {
                                            $status_class = 'success';
                                        }

                                        if ($student->status === 'pending') {
                                            $status_class = 'warning';
                                        }

                                        if ($student->status === 'inactive') {
                                            $status_class = 'secondary';
                                        }

                                        ?>

                                        <span
                                            class="badge text-bg-<?= $status_class; ?>">

                                            <?= ucfirst(
                                                html_escape(
                                                    $student->status
                                                )
                                            ); ?>

                                        </span>

                                    </td>


                                    <td class="text-end px-3">

                                        <div
                                            class="btn-group"
                                            role="group">

                                            <a
                                                href="<?= site_url(
                                                    'students/view/' .
                                                    $student->id
                                                ); ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View">

                                                <i class="fas fa-eye"></i>

                                            </a>


                                            <a
                                                href="<?= site_url(
                                                    'students/edit/' .
                                                    $student->id
                                                ); ?>"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Edit">

                                                <i class="fas fa-edit"></i>

                                            </a>


                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Archive">

                                                <i class="fas fa-archive"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-5">

                                    <div class="text-muted">

                                        <i
                                            class="fas fa-user-graduate
                                                   fa-2x
                                                   mb-3">
                                        </i>

                                        <h6 class="fw-semibold">

                                            No students found

                                        </h6>

                                        <p class="mb-0">

                                            Try changing your search
                                            or filter.

                                        </p>

                                    </div>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- =================================================
             Table Footer
        ================================================== -->

        <div class="card-footer bg-white">

            <div
                class="d-flex
                       flex-column
                       flex-md-row
                       justify-content-between
                       align-items-md-center
                       gap-3">

                <small class="text-muted">

                    Showing
                    <?= count($students); ?>
                    of
                    <?= $total_students; ?>
                    students

                </small>


                <nav aria-label="Student pagination">

                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item disabled">

                            <span class="page-link">

                                Previous

                            </span>

                        </li>

                        <li class="page-item active">

                            <span class="page-link">

                                <?= $current_page; ?>

                            </span>

                        </li>

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="?page=<?= $current_page + 1; ?>">

                                Next

                            </a>

                        </li>

                    </ul>

                </nav>

            </div>

        </div>

    </div>

</div>