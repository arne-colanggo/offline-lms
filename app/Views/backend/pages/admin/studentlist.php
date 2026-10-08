<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>

<!-- #header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Students</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Students
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
                        List of Students
                    </div>
                    <div class="pull-right">
                        <a href="<?= route_to('student.profile') ?>" class="btn btn-default btn-sm p-0 mr-10"
                            role="button" id="add_student_btn"><i class="fa fa-plus-circle"></i> Add Student</a>

                        <a href="" class="btn btn-default btn-sm p-0" role="button" id="add_student_password"><i
                                class="icon-copy dw dw-password"></i> Generate Password for New Students</a>

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
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= view('backend/pages/modal/add_enrollment_modal') ?>

<!-- #end grade level -->
<?= $this->endSection() ?>
<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui-1.13.2/jquery-ui.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui-1.13.2/jquery-ui.structure.min.css">
<link rel="stylesheet" href="\extra-assets\jquery-ui-1.13.2\jquery-ui-1.13.2\jquery-ui.theme.min.css">
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
        ajax: "<?= route_to('student.list'); ?>",

        fnCreatedRow: function (row, data, index) {
            $('td', row).eq(0).html(index + 1);

        },
        columnDefs: [
            { ordering: false, targets: [0, 1, 2, 3] },
        ],
    });


    $(document).on('click', '.deleteStudentBtn', function (e) {
        e.preventDefault();

        var student_id = $(this).data('id');
        var url = "<?= route_to('delete-student'); ?>";
        swal({
            title: "Are you sure?",
            html: "You want tod delete this student?",
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, Delete',
            cancelButtonColor: '#d33',
            confirmButtonColor: '#3085d6',
            width: 400,
            allowOutsideClick: false,
        }).then(function (result) {
            if (result.value) {
                $.get(url, { id: student_id }, function (response) {
                    if (response.status == 1) {
                        Students_DT.ajax.reload(null, false);
                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                }, 'json');
            }
        });
    });


    $(document).on('click', '.enrolStudentBtn', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var modal = $('body').find('div#add_enrollment_modal');
        var modal_title = 'Enroll Learner';
        var modal_btn_text = 'ENROL';
        var selectsection = modal.find('select[name="section"]');
        var selectsy = modal.find('select[name="schoolyear"]');
        var url = '<?= route_to('get-parent-sections') ?>';
        modal.find('#studentid').val(id);
        var urlschoolyear = '<?= route_to('get-parent-school-year') ?>';
        $.getJSON(url, { parent_grade_level_id: null }, function (response) {
            selectsection.find('option').remove();
            selectsection.html(response.data);
        });
        $.getJSON(urlschoolyear, { schoolyearid: null }, function (response) {
            selectsy.find('option').remove();
            selectsy.html(response.data);
        });


        modal.find('.modal-title').html(modal_title);
        modal.find('.modal-footer > button.action').html(modal_btn_text);
        modal.find('input[type="text"]').val('');
        modal.find('span.error-text').html('');
        modal.modal('show');

    })



    $('#add_enrollment_form').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var formdata = new FormData(form);
        var modal = $('body').find('div#add_enrollment_modal');
        formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            dataType: 'json',  // what to expect back from the server
            contentType: false,
            processData: false,
            cache: false,
            data: formdata,
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                //Udpate CSRF 
                $('.ci_csrf_data').val(response.token);

                if ($.isEmptyObject(response.error)) {

                    if (response.status == 1) {
                        $(form)[0].reset();
                        modal.modal('hide');
                        toastr.success(response.msg);

                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);
                    });
                }

            }
        });

    });


    $(document).on('click', '#add_student_password', function (e) {
        e.preventDefault();
        $url = '<?= route_to('generate-password-new-student') ?>';
        $.get($url, function (response) {
            if (response.status == 1) {
                toastr.success(response.msg);
            } else {
                toastr.success('Unable to generate password for the new students');
            }
        }, 'json');
    });
</script>
<?= $this->endSection() ?>