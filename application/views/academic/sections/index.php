<?php
$allGrades = [];
foreach ($grade_options_by_year as $yearGrades) {
    $allGrades = array_merge($allGrades, $yearGrades);
}
$allGrades = array_values(array_unique($allGrades));
sort($allGrades, SORT_NATURAL);
$displayGrades = $selected_year !== ''
    ? ($grade_options_by_year[$selected_year] ?? [])
    : $allGrades;
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= html_escape($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= html_escape($error_message); ?>
        <?php if (!empty($validation_error)): ?><div class="small mt-1"><?= html_escape($validation_error); ?></div><?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Section directory</h2>
                <p class="text-secondary small mb-0">Sections use school years and grade levels configured under Fee Configurations.</p>
            </div>
            <button type="button" class="btn btn-primary" id="addSectionButton">
                <i class="fas fa-plus me-2" aria-hidden="true"></i>Add section
            </button>
        </div>

        <form method="get" action="<?= site_url('academic/sections'); ?>" class="row g-2 align-items-end mt-2">
            <div class="col-12 col-md-4 col-xl-3">
                <label for="filterYear" class="form-label small fw-semibold">School year</label>
                <select name="year" id="filterYear" class="form-select">
                    <option value="">All school years</option>
                    <?php foreach ($school_years as $year): ?>
                        <option value="<?= html_escape($year); ?>" <?= $selected_year === $year ? 'selected' : ''; ?>><?= html_escape($year); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 col-xl-2">
                <label for="filterGrade" class="form-label small fw-semibold">Grade</label>
                <select name="grade" id="filterGrade" class="form-select">
                    <option value="">All grades</option>
                    <?php foreach ($displayGrades as $grade): ?>
                        <option value="<?= html_escape($grade); ?>" <?= $selected_grade === $grade ? 'selected' : ''; ?>>Grade <?= html_escape($grade); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 col-xl-2">
                <label for="filterStatus" class="form-label small fw-semibold">Status</label>
                <select name="status" id="filterStatus" class="form-select">
                    <option value="">All statuses</option>
                    <option value="Active" <?= $selected_status === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?= $selected_status === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-md-5 col-xl-3">
                <label for="sectionSearch" class="form-label small fw-semibold">Search</label>
                <input type="search" name="q" id="sectionSearch" class="form-control" value="<?= html_escape($search); ?>" placeholder="Section name">
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-filter me-1" aria-hidden="true"></i>Filter</button>
                <a href="<?= site_url('academic/sections'); ?>" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <div>
            <h2 class="h6 fw-bold mb-0">Academic sections</h2>
            <span class="text-secondary small"><?= count($sections); ?> matching <?= count($sections) === 1 ? 'section' : 'sections'; ?></span>
        </div>
        <span class="badge text-bg-light border text-secondary">Academic setup</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-3">School year</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th class="text-center">Enrolled students</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sections)): ?>
                    <tr><td colspan="6" class="py-5 text-center text-secondary">No sections match these filters. Add a section to get started.</td></tr>
                <?php else: ?>
                    <?php foreach ($sections as $section): ?>
                        <?php $active = strtolower((string) $section->status) === 'active'; ?>
                        <tr>
                            <td class="px-3 fw-semibold text-nowrap"><?= html_escape($section->year); ?></td>
                            <td class="text-nowrap">Grade <?= html_escape($section->grade); ?></td>
                            <td class="fw-semibold"><?= html_escape($section->section); ?></td>
                            <td class="text-center"><span class="badge rounded-pill text-bg-light border"><?= number_format((int) $section->enrolled_count); ?></span></td>
                            <td><span class="badge rounded-pill <?= $active ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?= html_escape($section->status); ?></span></td>
                            <td class="text-end pe-3 text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary edit-section-button"
                                    data-id="<?= (int) $section->id; ?>"
                                    data-year="<?= html_escape($section->year); ?>"
                                    data-grade="<?= html_escape($section->grade); ?>"
                                    data-section="<?= html_escape($section->section); ?>"
                                    data-status="<?= html_escape($section->status); ?>">
                                    <i class="fas fa-pen me-1" aria-hidden="true"></i>Edit
                                </button>
                                <form method="post" action="<?= site_url('academic/sections/status'); ?>" class="d-inline">
                                    <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                                    <input type="hidden" name="id" value="<?= (int) $section->id; ?>">
                                    <input type="hidden" name="status" value="<?= $active ? 'Inactive' : 'Active'; ?>">
                                    <button type="submit" class="btn btn-sm <?= $active ? 'btn-outline-secondary' : 'btn-outline-success'; ?>" data-confirm-status="<?= $active ? 'deactivate' : 'activate'; ?>">
                                        <?= $active ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-secondary">
        Inactive sections stay in existing enrollment records and are hidden from new enrollment choices.
    </div>
</section>

<div class="modal fade" id="sectionModal" tabindex="-1" aria-labelledby="sectionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= site_url('academic/sections/save'); ?>" id="sectionForm">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5 fw-bold" id="sectionModalTitle">Add section</h2>
                        <p class="small text-secondary mb-0">Choose a configured year and grade.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
                    <input type="hidden" name="id" id="sectionId" value="">
                    <div class="mb-3">
                        <label for="sectionYear" class="form-label fw-semibold">School year</label>
                        <select name="year" id="sectionYear" class="form-select" required>
                            <option value="">Select school year</option>
                            <?php foreach ($school_years as $year): ?>
                                <option value="<?= html_escape($year); ?>"><?= html_escape($year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sectionGrade" class="form-label fw-semibold">Grade</label>
                        <select name="grade" id="sectionGrade" class="form-select" required disabled>
                            <option value="">Select a school year first</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sectionName" class="form-label fw-semibold">Section name</label>
                        <input type="text" name="section" id="sectionName" class="form-control" maxlength="100" placeholder="For example, Sampaguita" required>
                    </div>
                    <div>
                        <label for="sectionStatus" class="form-label fw-semibold">Status</label>
                        <select name="status" id="sectionStatus" class="form-select" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1" aria-hidden="true"></i>Save section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="application/json" id="sectionGradeOptions"><?= json_encode($grade_options_by_year, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
