<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>

<!-- #header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>List of Enrolled Students in
                    <?= $section->name ?>: S.Y.:
                    <?= $schoolyear->name ?>
                </h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        List of Enrolled Students
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- #endheader -->

<!-- # add Section -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">
                        Section
                    </div>
                    <div class="pull-right ml-4">
                        <a href="<?= route_to('batch.enroll.student.section') . "/?id=" . $section->id ?>"
                            class="btn btn-default btn-sm p-0" id="btn_upload_students"><i class="fa fa-upload"></i>
                            Upload Students <a>
                    </div>
                    <div class="pull-right">
                        <button class="btn btn-default btn-sm p-0" id="enrol_student_btn"><i
                                class="fa fa-plus-circle"></i> Enrol Student <button>
                    </div>
                </div>
            </div>
            <div class="card-body pb-20">

                <table class="table table-borderless table-hover table-striped" id="students_table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">LRN</th>
                            <th scope="col">Name</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Address</th>
                            <th scope="col">Action</th>
                            <th scope="col">Un-enrol</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?= view('backend/pages/modal/student_list_modal.php'); ?>
<!-- #end grade level -->
<?= $this->endSection() ?>
<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui-1.13.2/jquery-ui.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui-1.13.2/jquery-ui.structure.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui-1.13.2/jquery-ui.theme.min.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script src="/extra-assets/jquery-ui-1.13.2/jquery-ui-1.13.2/jquery-ui.min.js"></script>

<script>



    var Students_DT = $('#students_table').DataTable({
        processing: true,
        serverSide: true,
        info: true,
        ajax: "<?= route_to('get.enrolled.students') . '/?id=' . $sectionid . '&sid=' . $schoolyearid; ?>",

        fnCreatedRow: function (row, data, index) {
            $('td', row).eq(0).html(index + 1);

        },
        columnDefs: [
            { ordering: false, targets: [0, 1, 2, 3] },
        ],
    });


    $(document).on('click', '#unenrol_btn', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        url = '<?= route_to('unenrol-section-student') ?>';

        swal({
            title: "Are you sure?",
            html: "You want to un-enrol this student?",
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, Un-enrol',
            cancelButtonColor: '#d33',
            confirmButtonColor: '#3085d6',
            width: 400,
            allowOutsideClick: false,
        }).then(function (result) {
            if (result.value) {
                $.get(url, { id: id }, function (response) {
                    if (response.status == 1) {
                        Students_DT.ajax.reload(null, false);

                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                }, 'json');
            }
        });
    })

    $(document).on('click', '#enrol_student_btn', function (e) {
        e.preventDefault()
        var modal = $(document).find('#student_list_modal');
        modal.modal('show');
    });


    var getStudents_DT = $('#get_students_table').DataTable({
        processing: true,
        serverSide: true,
        info: true,
        ajax: "<?= route_to('student.list'); ?>",

        fnCreatedRow: function (row, data, index) {
            $('td', row).eq(0).html(index + 1);

        },
        columnDefs: [
            { ordering: false, targets: [0, 1, 2, 3] },
            { visible: false, targets: [5] },

        ],
    });

    $(document).on('click', '.section_enrolStudentBtn', function (e) {
        e.preventDefault();
        var studentid = $(this).attr('data-id');
        var formdata = new FormData();
        var url = '<?= route_to('post.single.enrollment') ?>';
        formdata.append('studentid', studentid);
        formdata.append('section', <?= $sectionid ?>);
        formdata.append('schoolyear', <?= $schoolyearid ?>);
        formdata.append('dateenrolled', '<?= $date_now ?>');

        $.ajax({
            type: 'POST',
            url: url,
            data: formdata,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status == 1) {
                    toastr.success(response.msg);
                    Students_DT.ajax.reload(null, false);
                } else {
                    let err_msg = '';
                    if (response.error) {

                        $.each(response.error, function (prefix, val) {
                            err_msg += '<p>' + val + '</p>';
                        });
                    }
                    toastr.error(
                        err_msg
                    );
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>