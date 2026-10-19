<?= $this->extend('backend/layout/pages-teacher-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4>
                    <?= esc($classdata->name) ?>
                </h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= route_to('classes') ?>">Classes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= esc($classdata->name) ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 col-sm-12 text-right">
            <button type="button" class="btn btn-primary" id="btn_edit_class" data-id="<?= $classdata->id ?>">
                <i class="icon-copy dw dw-edit2"></i> Edit
            </button>
            <button type="button" class="btn btn-danger" id="btn_delete_class" data-id="<?= $classdata->id ?>"
                data-name="<?= esc($classdata->name) ?>">
                <i class="icon-copy dw dw-delete-3"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Class info -->
<div class="card-box mb-20 pd-20">
    <div class="row">
        <div class="col-md-3 mb-10">
            <div class="font-12 text-secondary">Class</div>
            <div class="weight-600" id="info_classname">
                <?= esc($classdata->name) ?>
            </div>
        </div>
        <div class="col-md-3 mb-10">
            <div class="font-12 text-secondary">Subject</div>
            <div class="weight-600">
                <?= esc($classdata->subjectname ?? '—') ?>
            </div>
        </div>
        <div class="col-md-3 mb-10">
            <div class="font-12 text-secondary">Section</div>
            <div class="weight-600">
                <?= esc($classdata->sectionname ?? '—') ?>
            </div>
        </div>
        <div class="col-md-3 mb-10">
            <div class="font-12 text-secondary">Grade Level</div>
            <div class="weight-600">
                <?= esc($classdata->gradelevelname ?? '—') ?>
            </div>
        </div>
    </div>
</div>

<div class="row pb-10">

    <!-- Enrolled -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a
                            href="<?= route_to('view.enrolled.students') . '/?id=' . $classdata->sectionid ?>">
                            <?= $enrolled ?>
                        </a></div>
                    <div class="font-14 text-secondary weight-500">Total Enrolled</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#00eccf" style="color: rgb(0, 236, 207);">
                        <i class="icon-copy dw dw-user-12"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">Manage</div>
                    <div class="font-14 text-secondary weight-500">
                        <a href="<?= route_to('attendance') . '/?id=' . $classdata->id ?>">Attendance</a>
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#ff5b5b" style="color: rgb(255, 91, 91);">
                        <span class="icon-copy dw dw-watch"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance tasks -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">Manage</div>
                    <div class="font-14 text-secondary weight-500">
                        <a href="<?= route_to('get.performancetasks') . '/?id=' . $classdata->id ?>">Performance
                            Tasks</a>
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon">
                        <i class="icon-copy dw dw-analytics-3" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quizzes -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">Manage</div>
                    <div class="font-14 text-secondary weight-500">
                        <a href="<?= route_to('get.class.quizzes', $classdata->id) ?>">Quizzes</a>
                        &middot;
                        <a href="<?= route_to('get.class.questions', $classdata->id) ?>">Questions</a>
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy dw dw-computer-1" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lesson plan -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">Manage</div>
                    <div class="font-14 text-secondary weight-500"><a target="_blank"
                            href="<?= route_to('bow.view') . '/?id=' . $classdata->subjectid ?>">Learning
                            Resources</a></div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy dw dw-notebook" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Virtual room -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a href="#" id="btn_virtual_room">Virtual Room</a></div>
                    <div class="font-14 text-secondary weight-500">Setup virtual classroom</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy fa fa-tablet" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class performance -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a
                            href="<?= route_to('class-performance') . '/?id=' . $classdata->id ?>">Performance</a>
                    </div>
                    <div class="font-14 text-secondary weight-500">Display performance</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy bi bi-award" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- E-Class record -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a
                            href="<?= route_to('export.eclass') . '/?id=' . $classdata->id ?>">E-Class</a></div>
                    <div class="font-14 text-secondary weight-500">Generate E-Class Record</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group generator -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a
                            href="<?= route_to('group-generator') . '/?id=' . $classdata->id ?>"
                            target="_blank">Group</a></div>
                    <div class="font-14 text-secondary weight-500">Group Generator</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy bi bi-people" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wheel of names -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a
                            href="<?= route_to('wheel.names') . '/?id=' . $classdata->id ?>" target="_blank">Pickers</a>
                    </div>
                    <div class="font-14 text-secondary weight-500">Wheel of Names</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy bi bi-shuffle" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>



<!-- Edit class modal -->
<div class="modal fade" id="edit_class_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" id="edit_class_form" action="<?= route_to('update.class') ?>" method="post">
            <?= csrf_field('ci_csrf_data') ?>
            <input type="hidden" name="classid" value="<?= $classdata->id ?>">
            <div class="modal-header">
                <h5 class="modal-title">Edit Class</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Class name</label>
                    <input type="text" name="classname" class="form-control" value="<?= esc($classdata->name) ?>">
                    <span class="text-danger error-text classname_error"></span>
                </div>
                <div class="form-group">
                    <label for="">Class Teacher</label>
                    <select class="form-control" name="teacherid" id="teacherid">

                    </select>
                </div>
                <!-- Subject and section are intentionally NOT editable: changing them
                     would change the enrolled students and the class's records. -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete class modal -->
<div class="modal fade" id="delete_class_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" id="delete_class_form" action="<?= route_to('delete-class') ?>" method="post">
            <?= csrf_field('ci_csrf_data') ?>
            <input type="hidden" name="classid" value="<?= $classdata->id ?>">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Class</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <p>You are about to delete <strong>
                        <?= esc($classdata->name) ?>
                    </strong>
                    (
                    <?= (int) $enrolled ?> enrolled student
                    <?= $enrolled == 1 ? '' : 's' ?>).
                </p>
                <p class="text-danger mb-0">This also removes its attendance, quizzes, performance tasks and scores.
                    This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, delete</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ---- helpers -------------------------------------------------------
    let $teachers = $('#teacherid');

    function postForm(form, onOk) {
        var $form = $(form);
        var formdata = new FormData(form);
        formdata.append($('.ci_csrf_data').first().attr('name'), $('.ci_csrf_data').first().val());
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            beforeSend: function () {
                toastr.remove();
                $form.find('span.error-text').text('');
                $form.find('button[type=submit]').prop('disabled', true);
            },
            complete: function () {
                $form.find('button[type=submit]').prop('disabled', false);
            },
            success: function (response) {
                $('.ci_csrf_data').val(response.token); // refresh CSRF hash
                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        toastr.success(response.msg);
                        onOk(response);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, value) {
                        $form.find('span.' + prefix + '_error').text(value);
                    });
                }
            },
            error: function () {
                toastr.error('Something went wrong. Please try again.');
            }
        });
    }

    // ---- edit class ----------------------------------------------------
    $(document).on('click', '#btn_edit_class', function () {
        $('#edit_class_modal').modal('show');
    });

    $('#edit_class_form').on('submit', function (e) {
        e.preventDefault();
        postForm(this, function () {
            $('#edit_class_modal').modal('hide');
            location.reload(); // refresh title, breadcrumb and info card
        });
    });

    // ---- delete class --------------------------------------------------
    $(document).on('click', '#btn_delete_class', function () {
        $('#delete_class_modal').modal('show');
    });

    $('#delete_class_form').on('submit', function (e) {
        e.preventDefault();
        postForm(this, function (response) {
            $('#delete_class_modal').modal('hide');
            setTimeout(function () {
                window.location.href = response.redirect || '<?= route_to('classes') ?>';
            }, 800);
        });
    });

    // ---- virtual room (your existing code, cleaned up) -----------------
    $(document).on('click', '#btn_virtual_room', function (e) {
        e.preventDefault();
        var modal = $('#set_virtual_room_modal');
        modal.find('.modal-title').text('Virtual Classroom');
        modal.find('input[name=classid]').val('<?= $classdata->id ?>');
        modal.modal('show');
    });

    $('#set_virtual_room_form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        postForm(form, function () {
            form.reset();
            $('#set_virtual_room_modal').modal('hide');
        });
    });
    $(document).ready(() => {
        $.getJSON('<?= route_to('teachers.list') ?>', function (res) {
            if (!res.teachers.length) {
                $teachers.prop('disabled', true).html('<option value="">No available teachers</option>');
                return;
            }
            let opts = '<option value="">Select teacher…</option>';
            res.teachers.forEach(t => opts += `<option value="${t.id}">${$('<div>').text(t.teacher).html()}</option>`);
            $teachers.prop('disabled', false).html(opts);

        });
    });
</script>
<?= $this->endSection() ?>