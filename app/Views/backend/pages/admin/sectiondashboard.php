<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4>
                    <?= esc($section->grade_name) ?> -
                    <?= esc($section->name) ?>
                </h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route_to('admin.home') ?>">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('grade-level-sections') . '/?id=' . $section->grade_level_id ?>">
                            <?= esc($section->grade_name) ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= esc($section->name) ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 col-sm-12 text-right">
            <!-- Point this to your Batch Enroll from SF1 route for this section -->
            <a href="<?= route_to('batch.enroll.student.section') . '/?id=' . $section->id ?>"
                class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person-plus"></i> Batch Enroll (SF1)
            </a>
        </div>
    </div>
</div>

<!-- Summary cards -->
<div class="row pb-10">
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">
                        <a href="<?= route_to('view.enrolled.students') . '/?id=' . $section->id ?>">
                            <?= $totalEnrolled ?>
                        </a>

                    </div>
                    <div class="font-14 text-secondary weight-500">Enrolled Learners</div>
                    <div class="font-12 text-muted">
                        <?= $male ?> Male &middot;
                        <?= $female ?> Female
                    </div>
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
                    <div class="weight-700 font-24 text-dark">
                        <?= $attendanceRate ?>%
                    </div>
                    <div class="font-14 text-secondary weight-500">Attendance Rate</div>
                    <div class="font-12 text-muted">This month</div>
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
                    <div class="weight-700 font-24 text-danger">
                        <?= count($frequent) ?>
                    </div>
                    <div class="font-14 text-secondary weight-500">Frequent Absentees</div>
                    <div class="font-12 text-muted">
                        <?= $threshold ?> or more absences
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#dc3545"><i class="icon-copy bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
    </div>
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

<div class="row">
    <!-- Upload utility -->
    <div class="col-lg-4 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Upload Attendance</h5>
            <p class="small text-muted">For
                <?= esc($section->grade_name) ?> -
                <?= esc($section->name) ?> only.
            </p>
            <form id="attendance_upload_form" method="post" action="<?= route_to('post.upload.attendance') ?>"
                enctype="multipart/form-data">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <input type="hidden" name="section_id" value="<?= $section->id ?>">
                <div class="form-group">
                    <label>Date <small class="text-muted">(used only if B2 is empty)</small></label>
                    <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>">
                    <span class="text-danger error-text date_error"></span>
                </div>
                <div class="form-group">
                    <label>Attendance File (SF2 / Excel)</label>
                    <input type="file" class="form-control-file form-control" name="attendance_file"
                        accept=".xls,.xlsx">
                    <span class="text-danger error-text attendance_file_error"></span>
                </div>
                <div class="progress mb-3" style="height:6px; display:none;" id="att_progress_wrap">
                    <div class="progress-bar" id="att_progress" style="width:0%"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Upload Attendance</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Monthly Attendance Rate</h5>
            <canvas id="trendChart" height="110"></canvas>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Absence Frequency (learners)</h5>
            <canvas id="bandChart" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6 mb-20">
        <div class="card-box height-100-p pd-20">
            <h5 class="h5 mb-3">Absences by Day of Week</h5>
            <canvas id="weekdayChart" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Class list with attendance summary -->
<div class="card-box mb-30">
    <div class="pd-20 d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="h5 mb-0">Class List and Attendance Summary</h5>
        <form method="get" class="form-inline">
            <input type="hidden" name="id" value="<?= $section->id ?>">
            <label class="mr-2 small">Flag absences &ge;</label>
            <input type="number" min="1" name="threshold" value="<?= $threshold ?>"
                class="form-control form-control-sm mr-2" style="width:80px">
            <button class="btn btn-sm btn-outline-primary">Apply</button>
        </form>
    </div>
    <div class="pb-20 px-3">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover" id="learner_table" style="width:100%">
                <thead>
                    <tr>
                        <th>LRN</th>
                        <th>Name</th>
                        <th>Sex</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Rate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($learners as $l):
                        $rate = $l->days > 0 ? round($l->present / $l->days * 100) : null; ?>
                        <tr>
                            <td>
                                <?= esc($l->lrn) ?>
                            </td>
                            <td>
                                <?= esc($l->lastname . ', ' . $l->firstname) ?>
                            </td>
                            <td>
                                <?= esc($l->gender) ?>
                            </td>
                            <td>
                                <?= $l->present ?>
                            </td>
                            <td><strong>
                                    <?= $l->absent ?>
                                </strong></td>
                            <td>
                                <?= $l->late ?>
                            </td>
                            <td>
                                <?= $rate === null ? '-' : $rate . '%' ?>
                            </td>
                            <td>
                                <?php if ($l->absent >= 10): ?>
                                    <span class="badge badge-danger">Critical</span>
                                <?php elseif ($l->absent >= $threshold): ?>
                                    <span class="badge badge-warning">At Risk</span>
                                <?php elseif ($l->absent > 0): ?>
                                    <span class="badge badge-info">Watch</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Good</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
    const trend = <?= json_encode($trend) ?>;
    const bands = <?= json_encode($bands) ?>;
    const weekday = <?= json_encode($weekday) ?>;

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trend.map(t => t.label),
            datasets: [{
                label: 'Attendance %', data: trend.map(t => t.rate),
                borderColor: '#1b00ff', backgroundColor: 'rgba(27,0,255,.1)', fill: true, tension: .3
            }]
        },
        options: { scales: { y: { min: 0, max: 100 } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('bandChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(bands).map(k => k + ' days'),
            datasets: [{ data: Object.values(bands), backgroundColor: ['#17a2b8', '#ffc107', '#fd7e14', '#dc3545'] }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('weekdayChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(weekday),
            datasets: [{ label: 'Absences', data: Object.values(weekday), backgroundColor: '#dc3545' }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    $('#learner_table').DataTable({ order: [[4, 'desc']], pageLength: 25 });

    // Attendance upload
    $('#attendance_upload_form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var formdata = new FormData(form);
        formdata.set($('.ci_csrf_data').attr('name'), $('.ci_csrf_data').val());

        $('#att_progress_wrap').show();
        $(form).find('button[type="submit"]').prop('disabled', true).text('Uploading...');

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (ev) {
                    if (ev.lengthComputable) {
                        $('#att_progress').css('width', Math.round(ev.loaded / ev.total * 100) + '%');
                    }
                });
                return xhr;
            },
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                $('.ci_csrf_data').val(response.token);
                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        toastr.success(response.msg);
                        form.reset();
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, value) {
                        $(form).find('span.' + prefix + '_error').text(value);
                    });
                }
            },
            error: function () {
                toastr.error('An error occurred while uploading attendance. Please try again.');
            },
            complete: function () {
                $('#att_progress_wrap').hide();
                $('#att_progress').css('width', '0%');
                $(form).find('button[type="submit"]').prop('disabled', false).text('Upload Attendance');
            }
        });
    });
</script>
<?= $this->endSection() ?>