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

                        <label for="academic_year" class="form-label small fw-semibold">
                            Academic Year
                        </label>

                        <select
                            name="academic_year"
                            id="academic_year"
                            class="form-select">

                            <option value="">
                                
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
                        id="grade_level"
                        class="form-select"
                        disabled>

                        <option value="">
                            
                        </option>

                    </select>

                </div>

                    <!-- Section -->

                    <div class="col-6 col-lg-2">

                        <label class="form-label small fw-semibold">

                            Section

                        </label>

                        <select
                            name="section"
                            id="section"
                            class="form-select"
                            disabled>

                            <option value="">
                              
                            </option>

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


                                    <td class="text-end">

                                        <!-- <a
                                            href="< ?= site_url(
                                                'students/view/' . $student->id
                                            ); ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View">

                                            <i class="fas fa-eye"></i>

                                        </a>


                                        <a
                                            href  site_url(
                                                'students/edit/' . $student->id
                                            ); ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit">

                                            <i class="fas fa-edit"></i>

                                        </a> -->


                                        <!-- Actions -->
                                        <div class="dropdown">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"
                                                title="Actions">

                                                <i class="fas fa-ellipsis-v"></i>

                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">

                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="<?= base_url(
                                                            'students/view/' . $student->id
                                                        ); ?>">

                                                        <i class="fas fa-eye me-2"></i>
                                                        View

                                                    </a>
                                                </li>

                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="<?= base_url(
                                                            'students/edit/' . $student->id
                                                        ); ?>">

                                                        <i class="fas fa-edit me-2"></i>
                                                        Edit

                                                    </a>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                <?php if ($student->status === 'active'): ?>

                                                    <li>

                                                        <button
                                                            type="button"
                                                            class="dropdown-item change-status-btn"
                                                            data-id="<?= $student->id; ?>"
                                                            data-name="<?= htmlspecialchars(
                                                                trim(
                                                                    $student->first_name .
                                                                    ', ' .
                                                                    $student->last_name
                                                                ),
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>"
                                                            data-lrn="<?= htmlspecialchars(
                                                                $student->lrn,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>"
                                                            data-grade="<?= htmlspecialchars(
                                                                $student->grade_level ?? '',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>"
                                                            data-current-status="active"
                                                            data-new-status="inactive">

                                                            <i class="fas fa-user-slash me-2"></i>
                                                            Deactivate

                                                        </button>

                                                    </li>

                                                <?php elseif ($student->status === 'inactive'): ?>

                                                    <li>

                                                        <button
                                                            type="button"
                                                            class="dropdown-item change-status-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#changeStatusModal"
                                                            data-id="<?= $student->id; ?>"
                                                            data-name="<?= htmlspecialchars(
                                                                trim(
                                                                    $student->first_name .
                                                                    ', ' .
                                                                    $student->last_name
                                                                ),
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>"
                                                            data-lrn="<?= htmlspecialchars(
                                                                $student->lrn,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>"
                                                            data-grade="<?= htmlspecialchars(
                                                                $student->grade_level ?? '',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>"
                                                            data-current-status="inactive"
                                                            data-new-status="active">

                                                            <i class="fas fa-user-check me-2"></i>
                                                            Activate

                                                        </button>

                                                    </li>

                                                <?php endif; ?>

                                            </ul>

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

                    <?php

                    /*
                    |--------------------------------------------------------------------------
                    | Build Query String
                    |--------------------------------------------------------------------------
                    */

                    $query_params = [];

                    foreach ($filters as $key => $value) {

                        if ($value !== '') {

                            $query_params[$key] = $value;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Previous Page
                    |--------------------------------------------------------------------------
                    */

                    $previous_params =
                        $query_params;

                    $previous_params['page'] =
                        $current_page - 1;


                    /*
                    |--------------------------------------------------------------------------
                    | Next Page
                    |--------------------------------------------------------------------------
                    */

                    $next_params =
                        $query_params;

                    $next_params['page'] =
                        $current_page + 1;


                    /*
                    |--------------------------------------------------------------------------
                    | Generate URLs
                    |--------------------------------------------------------------------------
                    */

                    $previous_url =
                        site_url('students') .
                        '?' .
                        http_build_query(
                            $previous_params
                        );


                    $next_url =
                        site_url('students') .
                        '?' .
                        http_build_query(
                            $next_params
                        );

                    ?>

                    <ul class="pagination pagination-sm mb-0">


                        <!-- Previous -->

                        <li
                            class="page-item
                                <?= $current_page <= 1
                                    ? 'disabled'
                                    : ''; ?>">

                            <?php if ($current_page <= 1): ?>

                                <span class="page-link">
                                    Previous
                                </span>

                            <?php else: ?>

                                <a
                                    class="page-link"
                                    href="<?= html_escape(
                                        $previous_url
                                    ); ?>">

                                    Previous

                                </a>

                            <?php endif; ?>

                        </li>


                        <!-- Current Page -->

                        <li class="page-item active">

                            <span class="page-link">

                                <?= $current_page; ?>

                            </span>

                        </li>


                        <!-- Next -->

                        <li
                            class="page-item
                                <?= (
                                    $total_pages === 0
                                    ||
                                    $current_page >= $total_pages
                                )
                                    ? 'disabled'
                                    : ''; ?>">

                            <?php if (
                                $total_pages === 0
                                ||
                                $current_page >= $total_pages
                            ): ?>

                                <span class="page-link">
                                    Next
                                </span>

                            <?php else: ?>

                                <a
                                    class="page-link"
                                    href="<?= html_escape(
                                        $next_url
                                    ); ?>">

                                    Next

                                </a>

                            <?php endif; ?>

                        </li>

                    </ul>

                </nav>

            </div>

        </div>

    </div>

    
    <!-- =====================================================
        Archive Student Modal
    ====================================================== -->
    <div
    class="modal fade"
    id="changeStatusModal"
    tabindex="-1"
    aria-labelledby="changeStatusModalLabel"
    aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form
                id="changeStatusForm"
                method="post">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="changeStatusModalLabel">

                        Change Status

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>

                </div>

                <div class="modal-body">

                    <?= form_hidden(
                        $this->security
                            ->get_csrf_token_name(),
                        $this->security
                            ->get_csrf_hash()
                    ); ?>

                    <p class="mb-3">
                        Are you sure you want to
                        <strong id="statusActionText"></strong>
                        this student?
                    </p>

                    <div class="mb-1">

                        <strong>LRN:</strong>
                        <span id="statusStudentLrn"></span>

                    </div>

                    <div class="mb-1">

                        <strong>Student:</strong>
                        <span id="statusStudentName"></span>

                    </div>

                    <div class="mb-3">

                        <strong>Grade:</strong>
                        <span id="statusStudentGrade"></span>

                    </div>

                    <div
                        class="alert alert-warning mb-3"
                        id="statusResultMessage">

                        The student will be marked as
                        <strong id="statusNewStatus"></strong>.

                    </div>

                    <div class="mb-3">

                        <label
                            for="statusReason"
                            class="form-label">

                            Reason
                            <span class="text-danger">*</span>

                        </label>

                        <textarea
                            name="reason"
                            id="statusReason"
                            class="form-control"
                            rows="4"
                            maxlength="500"
                            required
                            placeholder="Enter the reason for changing the student's status..."></textarea>

                        <div class="form-text">
                            Maximum 500 characters.
                        </div>

                    </div>

                    <input
                        type="hidden"
                        name="new_status"
                        id="newStudentStatus">

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="btn"
                        id="changeStatusSubmit">

                        <span id="changeStatusSubmitText">
                            Submit
                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</div>
<script>

    const classAssignmentMode = 'filter';

    const submittedEnrollment = {
        academic_year:
            <?= json_encode(
                $filters['academic_year'] ?? ''
            ); ?>,

        grade_level:
            <?= json_encode(
                $filters['grade_level'] ?? ''
            ); ?>,

        section:
            <?= json_encode(
                $filters['section'] ?? ''
            ); ?>
    };

    const BASE_URL =
        "<?= base_url(); ?>";
</script>


<script src="<?= base_url(
    'assets/js/student/class_assignment.js'
); ?>"></script>