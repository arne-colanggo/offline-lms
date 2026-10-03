<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Settings</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Settings
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="pd-20 card-box mb-4">

    <div class="tab">
        <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#general_settings" role="tab"
                    aria-selected="true">General Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#logo_favicon" role="tab" aria-selected="false">Logo and
                    Favicon</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="general_settings" role="tabpanel">
                <div class="pd-20">
                    <form action="<?= route_to('post.update.settings') ?>" method="post" id="general_settings_form">
                        <input type="hidden" name="<?= csrf_token(); ?>" value="<?= csrf_hash(); ?>"
                            class="ci_csrf_data">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">School Name</label>
                                    <input type="text" name="schoolname" id="schoolname" class="form-control"
                                        placeholder="School Name" value="<?= get_settings()->schoolname ?>">
                                    <span class="text-danger error-text schoolname_error"></span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Address</label>
                                    <input type="text" name="schooladdress" id="schooladdress" class="form-control"
                                        placeholder="Enter school address" value="<?= get_settings()->schooladdress ?>">
                                    <span class="text-danger error-text schooladdress_error"></span>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Phone No.</label>
                                    <input type="text" name="phone" id="phone" class="form-control"
                                        placeholder="Enter Phone Number" value="<?= get_settings()->phone ?>">
                                    <span class="text-danger error-text phone_error"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Email.</label>
                                    <input type="text" name="email" id="email" class="form-control"
                                        placeholder="Enter Email" value="<?= get_settings()->email ?>">
                                    <span class="text-danger error-text email_error"></span>
                                </div>
                            </div>

                            <div class="col-md-4">



                                <div class="form-group" data-select2-id="36">

                                    <div class="d-flex justify-content-between align-items-center mb-1">

                                        <label class="mb-0">
                                            School Year
                                        </label>

                                    </div>

                                    <select class="custom-select2 form-control select2-hidden-accessible"
                                        name="schoolyear" id="schoolyear" style="width: 100%; height: 38px"
                                        data-select2-id="1" tabindex="-1" aria-hidden="true">

                                    </select>

                                    <span class="text-danger error-text schoolyear_error"></span>

                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">School Head.</label>
                                    <input type="text" name="schoolhead" id="schoolhead" class="form-control"
                                        placeholder="School head name" value="<?= get_settings()->schoolhead ?>">
                                    <span class="text-danger error-text schoolhead_error"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Designation</label>
                                    <input type="text" name="schoolheaddesignation" id="schoolheaddesignation"
                                        class="form-control" placeholder="Designation"
                                        value="<?= get_settings()->schoolheaddesignation ?>">
                                    <span class="text-danger error-text schoolheaddesignation_error"></span>
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save General Settings</button>
                        </div>

                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="logo_favicon" role="tabpanel">
                <div class="pd-20">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Set Logo</h5>
                            <div class="mb-2 mt-1" style="max-width:200px" id="img-holder">

                                <img src="<?= '/images/settings/' . get_settings()->logo ?>" class="img-thumbnail"
                                    id="logo-image">

                            </div>
                            <form action="<?= route_to('update.logo') ?>" method="post" enctype="mulitpart/form-data"
                                id="change_logo_form">
                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>"
                                    class="ci_csrf_data">
                                <div class="form-group mb-2">
                                    <input type="file" name="logo" id="" class="form-control">
                                    <span class="text-danger error-text"></span>
                                </div>
                                <button type="submit" class="btn btn-primary">Change Logo</button>

                            </form>
                        </div>

                        <div class="col-md-6">
                            <h5>Favicon Image</h5>
                            <div class="mb-2 mt-1" style="max-width:100px" id="favicon_image_preview">
                                <img src="/images/settings/<?= get_settings()->favicon ?>" alt="" srcset="">
                            </div>
                            <form action="<?= route_to('update.favicon') ?>" method="post" id="change_favicon_form"
                                enctype="multipart/form-data">
                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                <div class="form-group">
                                    <input type="file" name="favicon" id="favicon" class="form-control">
                                    <span class="text-danger error-text"></span>
                                </div>
                                <button type="submit" class="btn btn-primary">Change Favicon</button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    $('#general_settings_form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var csrfName = $('.ci_csrf_data').attr('name'); //CSRF Token name
        var csrfHash = $('.ci_csrf_data').val();
        var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formdata,
            processData: false,
            dataType: 'json',
            contentType: false,
            cache: false,
            beforeSend: function () {
                toastr.remove()
                $(form).find('span.error-text').val('');

            },
            success: function (response) {
                //console.log(response);

                $('.ci_csrf_data').val(response.token);
                if ($.isEmptyObject(response.errors)) {
                    if (response.status == 1) {
                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.errors, function (prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);
                    });
                }
            }

        });

    });



    $('input[type="file"][name="favicon"]').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const imageUrl = e.target.result;
                $("#favicon_image_preview").html(`<img src="${imageUrl}" alt="Image">`);
            }
            reader.readAsDataURL(file);
        }
    });


    $('#change_favicon_form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);
        var inputFileVal = $(form).find('input[type="file"][name="favicon"]').val();

        if (inputFileVal.length > 0) {
            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: formdata,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success: function (response) {
                    $('.ci_csrf_data').val(response.token);
                    if (response.status == 1) {
                        toastr.success(response.msg);
                        $(form)[0].reset();

                    } else {
                        toastr.error(response.msg);
                    }
                },
            });
        } else {
            $(form).find('span.error-text').text('Please select image file for Favicon. PNG file type is recommended');
        }

    });

    $('#change_logo_form').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);

        var inputFileVal = $(form).find('input[type="file"][name="logo"]').val();



        if (inputFileVal.length > 0) {

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                dataType: 'json',
                contentType: false,
                processData: false,
                data: formdata,
                beforeSend: function () {
                    toastr.remove();
                    $(form).find('span.error-text').val('');
                },
                success: function (response) {
                    $('.ci_csrf_data').val(response.token);

                    if (response.status == 1) {
                        toastr.success(response.msg);
                        $(form)[0].reset();
                    } else {
                        toastr.error(response.msg);
                    }


                },


            });

        } else {
            $(form).find('span.error-text').text('Please select logo image file. PNG file type is recommended!');
        }


    });

    $('#social_media_form').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: formdata,
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                $('.ci_csrf_data').val(response.token);

                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);

                    });
                }

            },
        });



    });

    // School Year

    $(document).ready(function (e) {

        var url_schoolyear = '<?= route_to('get.cboschoolyear'); ?>';
        var select = $('#schoolyear');
        $.get(url_schoolyear, function (response) {
            select.find('option').remove();
            select.html(response.data);
            select.val('<?= get_settings()->schoolyear ?>');
        }, 'json');
    });

</script>

<?= $this->endSection() ?>