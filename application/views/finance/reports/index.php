<div class="container-fluid">
    <?php if (!empty($filter_error)): ?>
        <div class="alert alert-danger" role="alert"><?= html_escape($filter_error); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?= site_url('finance/reports'); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label small fw-semibold" for="report-school-year">School year</label>
                        <select class="form-select" id="report-school-year" name="school_year">
                            <option value="">All years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= html_escape($year->year); ?>" <?= $filters['school_year'] === $year->year ? 'selected' : ''; ?>><?= html_escape($year->year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label small fw-semibold" for="report-grade">Grade</label>
                        <select class="form-select" id="report-grade" name="grade" <?= $filters['school_year'] === '' ? 'disabled' : ''; ?>>
                            <option value="">All grades</option>
                            <?php foreach ($grades as $grade): ?>
                                <option value="<?= html_escape($grade->grade); ?>" <?= $filters['grade'] === $grade->grade ? 'selected' : ''; ?>><?= html_escape($this->fee_configuration_model->grade_label($grade->grade)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label small fw-semibold" for="report-section">Section</label>
                        <select class="form-select" id="report-section" name="section" <?= $filters['school_year'] === '' || $filters['grade'] === '' ? 'disabled' : ''; ?>>
                            <option value="">All sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= html_escape($section->section); ?>" <?= $filters['section'] === $section->section ? 'selected' : ''; ?>><?= html_escape($section->section); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label small fw-semibold" for="report-date-from">Collection from</label>
                        <input class="form-control" type="date" id="report-date-from" name="date_from" value="<?= html_escape($filters['date_from']); ?>">
                    </div>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label class="form-label small fw-semibold" for="report-date-to">Collection to</label>
                        <input class="form-control" type="date" id="report-date-to" name="date_to" value="<?= html_escape($filters['date_to']); ?>">
                    </div>
                    <div class="col-12 col-xl-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1" aria-hidden="true"></i>Apply</button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('finance/reports'); ?>">Clear</a>
                    </div>
                </div>
                <p class="form-text mb-0 mt-3">The date range filters collections only. Assessed fees and outstanding balances show current totals for the selected enrollments. Voided payments are excluded.</p>
            </form>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <?php foreach ([
            ['label' => 'Assessed fees', 'value' => $report['assessed'], 'class' => 'primary'],
            ['label' => 'Collected in date range', 'value' => $report['collected'], 'class' => 'success'],
            ['label' => 'Outstanding balance', 'value' => $report['outstanding'], 'class' => 'warning']
        ] as $card): ?>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="small text-muted mb-1"><?= html_escape($card['label']); ?></div>
                    <div class="h4 fw-bold text-<?= $card['class']; ?> mb-0">PHP <?= number_format((float) $card['value'], 2); ?></div>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Collections and balances</h2>
                <p class="small text-muted mb-0"><?= number_format(count($report['rows'])); ?> school year, grade, and section groups</p>
            </div>
            <form method="get" action="<?= site_url('finance/reports/csv'); ?>">
                <?php foreach (['school_year', 'grade', 'section', 'date_from', 'date_to'] as $key): ?>
                    <input type="hidden" name="<?= $key; ?>" value="<?= html_escape($filters[$key]); ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-download me-1" aria-hidden="true"></i>Download CSV</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="px-3">School year</th><th>Grade</th><th>Section</th>
                    <th class="text-end">Assessed (PHP)</th><th class="text-end">Collected (PHP)</th><th class="text-end px-3">Outstanding (PHP)</th>
                </tr></thead>
                <tbody>
                    <?php if (!empty($report['rows'])): ?>
                        <?php foreach ($report['rows'] as $row): ?>
                            <tr>
                                <td class="px-3"><?= html_escape($row->school_year); ?></td>
                                <td><?= html_escape($row->grade); ?></td>
                                <td><?= html_escape($row->section); ?></td>
                                <td class="text-end"><?= number_format((float) $row->assessed, 2); ?></td>
                                <td class="text-end"><?= number_format((float) $row->collected, 2); ?></td>
                                <td class="text-end px-3 fw-semibold"><?= number_format((float) $row->outstanding, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">No fee assessments match these filters.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($report['rows'])): ?>
                    <tfoot class="table-light fw-bold"><tr>
                        <td class="px-3" colspan="3">Total</td>
                        <td class="text-end"><?= number_format((float) $report['assessed'], 2); ?></td>
                        <td class="text-end"><?= number_format((float) $report['collected'], 2); ?></td>
                        <td class="text-end px-3"><?= number_format((float) $report['outstanding'], 2); ?></td>
                    </tr></tfoot>
                <?php endif; ?>
            </table>
        </div>
    </section>
</div>
