<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>

<!-- #header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Section</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Section
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
                    <div class="pull-right">
                        <a href="" class="btn btn-default btn-sm p-0" role="button" id="add_section_btn"><i
                                class="fa fa-plus-circle"></i> Add Section</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless table-hover table-striped" id="section_table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Grade Level</th>
                                <th scope="col">Enrolled </th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?= view('backend/pages/modal/add_section_modal.php') ?>
<?= view('backend/pages/modal/edit_section_modal.php') ?>

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


    $('#add_section_btn').on('click', function (e) {
        e.preventDefault();
        var modal = $('body').find('div#add_section_modal');
        var modal_title = 'Add Section';
        var modal_btn_text = 'ADD';

        var select = modal.find('select[name="parent_grade_level"]');
        var url = '<?= route_to('get.parent.gradelevel') ?>';
        $.getJSON(url, { parent_grade_level_id: null }, function (response) {
            select.find('option').remove();
            select.html(response.data);
        });

        modal.find('.modal-title').html(modal_title);
        modal.find('.modal-footer > button.action').html(modal_btn_text);
        modal.find('input[type="text"]').val('');
        modal.find('span.error-text').html('');
        modal.modal('show');

    });



    $('#add_section_form').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var formdata = new FormData(form);
        var modal = $('body').find('div#add_section_modal');
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
                        Section_DT.ajax.reload(null, false);

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

    var Section_DT = $('#section_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "<?= route_to('get.sections'); ?>",
        dom: "Bfrtip",
        info: true,
        fnCreatedRow: function (row, data, index) {
            $('td', row).eq(0).html(index + 1);

        },
        columnDefs: [
            { ordering: false, targets: [0, 1, 2, 3] },
        ],
    });

    $(document).on('click', '.editSectionBtn', function (e) {
        e.preventDefault();

        var id = $(this).attr('data-id');
        var url = "<?= route_to('get.section') ?>";
        var get_parent_grade_url = '<?= route_to('get.parent.gradelevel') ?>';

        $.get(url, { 'section_id': id }, function (response) {
            var modal_title = "Edit Section";
            var modal_btn_text = "Save Changes";
            var modal = $('body').find('div#edit_section_modal');
            modal.find('.modal-title').html(modal_title);
            modal.find('input[name=section_id]').val(id);
            modal.find('.modal-footer > button.action').html(modal_btn_text);
            modal.find('span.error-text').text('');

            var select = modal.find('select[name="parent_grade_level"]');

            $.getJSON(url, { 'section_id': id }, function (respsonse) {
                modal.find('input[type="text"][name="section_name"]').val(response.data.name);
                modal.find('form').find('intput[type="hidden"][name="grade_level_id"]').val(response.data.id);
                $.getJSON(get_parent_grade_url, { 'parent_grade_level_id': response.data.grade_level_id }, function (response) {
                    select.find('option').remove();
                    select.html(response.data);
                });
                modal.modal('show');
            });


        });

    });

    $('#edit_section_form').on('submit', function (e) {
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var modal = $('body').find('#edit_section_modal');
        var form = this;
        var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            data: formdata,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                //Update CSRF hash
                $('.ci_csrf_data').val(response.token);
                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        $(form)[0].reset();
                        modal.modal('hide');
                        toastr.success(response.msg);
                        Section_DT.ajax.reload(null, false);

                    } else {
                        toastr.error(Response.msg);
                    }

                } else {
                    $.each(response.error, function (prefix, value) {
                        $(form).find('span.' + prefix + '_error').text(value);
                    });
                }

            },
        });

    });


    $(document).on('click', '.deleteSectionBtn', function (e) {
        e.preventDefault();

        var id = $(this).data('id');
        var url = "<?= route_to('delete.section'); ?>";
        swal({
            title: "Are you sure?",
            html: "You want tod delete this Section?",
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
                $.get(url, { section_id: id }, function (response) {
                    if (response.status == 1) {
                        Section_DT.ajax.reload(null, false);

                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                }, 'json');
            }
        });
    });


</script>
<?= $this->endSection() ?>