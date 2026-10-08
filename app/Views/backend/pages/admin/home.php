<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-7 col-sm-12">
            <div class="title">
                <h4>Dashboard</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route_to('admin.home') ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-5 col-sm-12 text-right">
            <a href="<?= route_to('upload.sf1') ?>" class="btn btn-outline-primary btn-sm mr-2">
                <i class="bi bi-person-plus"></i> Student Registration (SF1)
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#announcementModal">
                <i class="bi bi-megaphone"></i> New Announcement
            </button>
        </div>
    </div>
</div>

<!-- Summary cards -->
<div class="row pb-10">
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= number_format($totalEnrolled) ?></div>
                    <div class="font-14 text-secondary weight-500">Total Enrolled</div>
                    <div class="font-12 text-muted"><?= $male ?> Male &middot; <?= $female ?> Female</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#00eccf"><i class="icon-copy bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= $totalGrades ?></div>
                    <div class="font-14 text-secondary weight-500">Grade Levels</div>
                    <div class="font-12 text-muted"><?= $totalSections ?> sections</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#1b00ff"><i class="icon-copy bi bi-layers"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">
                        <?= $rateToday === null ? '-' : $rateToday . '%' ?>
                    </div>
                    <div class="font-14 text-secondary weight-500">Attendance Today</div>
                    <div class="font-12 text-muted">
                        <?= $rateToday === null ? 'No attendance uploaded yet' : "$presentToday present &middot; $absentToday absent &middot; $lateToday late" ?>
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#28a745"><i class="icon-copy bi bi-calendar-check"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-danger"><?= $frequentCount ?></div>
                    <div class="font-14 text-secondary weight-500">Frequent Absentees</div>
                    <div class="font-12 text-muted"><?= $threshold ?> or more absences</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#dc3545"><i class="icon-copy bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second row of small stats -->
<div class="row">
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= $rateMonth ?>%</div>
                    <div class="font-14 text-secondary weight-500">Attendance This Month</div>
                    <div class="font-12 text-muted"><?= date('F Y') ?></div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#17a2b8"><i class="icon-copy bi bi-graph-up"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">
                        <?= $totalSections > 0 ? round($totalEnrolled / $totalSections) : 0 ?>
                    </div>
                    <div class="font-14 text-secondary weight-500">Avg. Class Size</div>
                    <div class="font-12 text-muted">Learners per section</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#fd7e14"><i class="icon-copy bi bi-diagram-3"></i></div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($totalTeachers !== null): ?>
        <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
            <div class="card-box height-100-p widget-style3">
                <div class="d-flex flex-wrap">
                    <div class="widget-data">
                        <div class="weight-700 font-24 text-dark"><?= $totalTeachers ?></div>
                        <div class="font-14 text-secondary weight-500">Teachers</div>
                        <div class="font-12 text-muted">
                            <?= $totalTeachers > 0 ? round($totalEnrolled / $totalTeachers, 1) : 0 ?> learners per teacher
                        </div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" style="color:#6f42c1"><i class="icon-copy bi bi-person-workspace"></i></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-20 text-dark">
                        <?= $lastDate ? date('M d, Y', strtotime($lastDate)) : 'No data yet' ?>
                    </div>
                    <div class="font-14 text-secondary weight-500">Last Attendance Date</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#ffc107"><i class="icon-copy bi bi-cloud-upload"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrollment charts -->
<div class="row">
    <div class="col-lg-8 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Enrollment by Grade Level</h5>
            <canvas id="gradeChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-4 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Gender Distribution</h5>
            <canvas id="genderChart" height="220"></canvas>
        </div>
    </div>
</div>

<!-- Attendance charts -->
<div class="row">
    <div class="col-lg-6 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Monthly Attendance Rate</h5>
            <canvas id="trendChart" height="140"></canvas>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Absences by Day</h5>
            <canvas id="weekdayChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Absences by Grade (This Month)</h5>
            <canvas id="absGradeChart" height="220"></canvas>
        </div>
    </div>
</div>

<!-- Announcements + Frequent absentees -->
<div class="row">
    <div class="col-lg-5 mb-20">
        <div class="card-box height-100-p pd-20">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="h5 mb-0">Announcements</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                    data-target="#announcementModal">Post</button>
            </div>
            <?php if (empty($announcements)): ?>
                <p class="text-muted small mb-0">No announcements yet. Use Post to share news with teachers, students or parents.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($announcements as $a): ?>
                        <li class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <div class="weight-600">
                                    <?php if ($a->is_pinned): ?><i class="bi bi-pin-angle-fill text-warning"></i><?php endif; ?>
                                    <?= esc($a->title) ?>
                                </div>
                                <button class="btn btn-link btn-sm text-danger p-0 delete-announcement"
                                    data-id="<?= $a->id ?>" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                            <div class="small text-muted mb-1">
                                <?= esc($a->audience) ?> &middot; <?= date('M d, Y g:i A', strtotime($a->created_at)) ?>
                            </div>
                            <div class="small"><?= esc(mb_strimwidth($a->body, 0, 140, '...')) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-7 mb-20">
        <div class="card-box height-100-p pd-20">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <h5 class="h5 mb-0">Frequent Absentees</h5>
                <form method="get" class="form-inline">
                    <label class="mr-2 small">Flag absences &ge;</label>
                    <input type="number" min="1" name="threshold" value="<?= $threshold ?>"
                        class="form-control form-control-sm mr-2" style="width:80px">
                    <button class="btn btn-sm btn-outline-primary">Apply</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Name</th>
                            <th>Grade &amp; Section</th>
                            <th>Absences</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($frequent)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No learners have reached <?= $threshold ?> absences.</td></tr>
                        <?php else: foreach ($frequent as $f): ?>
                            <tr>
                                <td><?= esc($f->lrn) ?></td>
                                <td><?= esc($f->lastname . ', ' . $f->firstname) ?></td>
                                <td><?= esc($f->grade_name . ' - ' . $f->section_name) ?></td>
                                <td><strong><?= $f->absences ?></strong></td>
                                <td>
                                    <?php if ($f->absences >= 10): ?>
                                        <span class="badge badge-danger">Critical</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">At Risk</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sections overview + recent enrollees -->
<div class="row">
    <div class="col-lg-7 mb-20">
        <div class="card-box height-100-p">
            <div class="pd-20"><h5 class="h5 mb-0">Sections Overview</h5></div>
            <div class="pb-20 px-3">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover" id="sections_table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Enrolled</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sections as $s): ?>
                                <tr>
                                    <td><?= esc($s->grade_name) ?></td>
                                    <td><?= esc($s->name) ?></td>
                                    <td><?= $s->total ?></td>
                                    <td>
                                        <a href="<?= route_to('section.dashboard') . '/?id=' . $s->id ?>"
                                            class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Recently Enrolled</h5>
            <?php if (empty($recent)): ?>
                <p class="text-muted small mb-0">No learners enrolled yet. Start with Batch Enroll (SF1).</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($recent as $r): ?>
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <div class="weight-600"><?= esc($r->lastname . ', ' . $r->firstname) ?></div>
                                <div class="small text-muted"><?= esc($r->grade_name . ' - ' . $r->section_name) ?></div>
                            </div>
                            <div class="small text-muted text-right">
                                <?= $r->created_at ? date('M d, Y', strtotime($r->created_at)) : '' ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Announcement modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" id="announcement_form" method="post" action="<?= route_to('post.announcement') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
            <div class="modal-header">
                <h5 class="modal-title">New Announcement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" maxlength="150">
                    <span class="text-danger error-text title_error"></span>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" name="body" rows="4"></textarea>
                    <span class="text-danger error-text body_error"></span>
                </div>
                <div class="form-group">
                    <label>Audience</label>
                    <select class="form-control" name="audience">
                        <option value="All">Everyone</option>
                        <option value="Teachers">Teachers</option>
                        <option value="Students">Students</option>
                        <option value="Parents">Parents</option>
                    </select>
                    <span class="text-danger error-text audience_error"></span>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_pinned" name="is_pinned" value="1">
                    <label class="custom-control-label" for="is_pinned">Pin to top</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    const byGrade    = <?= json_encode($byGrade) ?>;
    const trend      = <?= json_encode($trend) ?>;
    const weekday    = <?= json_encode($weekday) ?>;
    const absByGrade = <?= json_encode($absByGrade) ?>;
    const gender     = { Male: <?= (int) $male ?>, Female: <?= (int) $female ?> };

    new Chart(document.getElementById('gradeChart'), {
        type: 'bar',
        data: {
            labels: byGrade.map(g => g.name),
            datasets: [{ label: 'Learners', data: byGrade.map(g => +g.total), backgroundColor: '#1b00ff' }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(gender),
            datasets: [{ data: Object.values(gender), backgroundColor: ['#17a2b8', '#e83e8c'] }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trend.map(t => t.label),
            datasets: [{
                label: 'Attendance %', data: trend.map(t => t.rate),
                borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,.1)', fill: true, tension: .3
            }]
        },
        options: { scales: { y: { min: 0, max: 100 } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('weekdayChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(weekday),
            datasets: [{ label: 'Absences', data: Object.values(weekday), backgroundColor: '#dc3545' }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('absGradeChart'), {
        type: 'bar',
        data: {
            labels: absByGrade.map(g => g.name),
            datasets: [{ label: 'Absences', data: absByGrade.map(g => +g.total), backgroundColor: '#fd7e14' }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    $('#sections_table').DataTable({ pageLength: 10, order: [[0, 'asc']] });

    // Post announcement
    $('#announcement_form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var formdata = new FormData(form);
        formdata.set($('.ci_csrf_data').attr('name'), $('.ci_csrf_data').val());
        var btn = $(form).find('button[type="submit"]');

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
                btn.prop('disabled', true).text('Posting...');
            },
            success: function (response) {
                $('.ci_csrf_data').val(response.token);
                if (response.status == 1) {
                    toastr.success(response.msg);
                    form.reset();
                    $('#announcementModal').modal('hide');
                    setTimeout(() => location.reload(), 900);
                } else if (response.error && !$.isEmptyObject(response.error)) {
                    $.each(response.error, function (prefix, value) {
                        $(form).find('span.' + prefix + '_error').text(value);
                    });
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function () {
                toastr.error('An error occurred while posting the announcement. Please try again.');
            },
            complete: function () {
                btn.prop('disabled', false).text('Post Announcement');
            }
        });
    });

    // Delete announcement
    $(document).on('click', '.delete-announcement', function () {
        if (!confirm('Delete this announcement?')) return;
        var id = $(this).data('id');
        var csrfName = $('.ci_csrf_data').attr('name');
        var data = { id: id };
        data[csrfName] = $('.ci_csrf_data').val();

        $.post('<?= route_to('delete.announcement') ?>', data, function (response) {
            $('.ci_csrf_data').val(response.token);
            if (response.status == 1) {
                toastr.success(response.msg);
                setTimeout(() => location.reload(), 700);
            } else {
                toastr.error(response.msg);
            }
        }, 'json').fail(function () {
            toastr.error('An error occurred while deleting the announcement.');
        });
    });
</script>
<?= $this->endSection() ?>