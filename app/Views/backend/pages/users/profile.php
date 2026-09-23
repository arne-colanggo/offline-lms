<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Profile</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        User Profile
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<form action="<?= route_to('post-student-information') ?>" method="post" id="post-student-information-form">
    <div class="row">
        <input type="hidden" id="id" name="id" value="<?= isset($profile) ? $profile->id : '0' ?>">
        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 mb-30">
            <div class="pd-20 card-box height-100-p">
                <div class="profile-photo">
                    <a href="javascript:;"
                        onclick="event.preventDefault(); document.getElementById('profile_file').click();"
                        class="edit-avatar"><i class="fa fa-pencil"></i></a>
                    <input type="file" name="profile_file" id="profile_file" class="d-none" style="opacity:0">
                    <img src="<?= (isset($profile->picture)) ? ($profile->picture == null) ? '/images/users/default-avatar.png' : '/images/students/' . $profile->picture : '/images/users/default-avatar.png' ?>"
                        alt="" class="avatar-photo ci-avatar-photo">

                </div>

                <div class="form-group text-center">
                    <input type="submit" class="btn btn-primary" value="Update Information">
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mb-30">
            <div class="card-box height-100-p overflow-hidden">


                <div class="profile-tab height-100-p">
                    <div class="tab height-100-p">
                        <ul class="nav nav-tabs customtab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#basic" role="tab">Basic
                                    Information</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#otherrelated" role="tab">Parent/Guardian
                                    Information</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#socialmedia" role="tab">Social Media
                                    Links</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <!-- Basic Tab start -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="pd-20">
                                    <ul class="profile-edit-list row">
                                        <li class="weight-500 col-md-6">

                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input class="form-control form-control-lg" type="text" id="firstname"
                                                    name="firstname"
                                                    value="<?= isset($profile) ? $profile->firstname : '' ?>">
                                                <span class="text-danger error-text firstname_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Middle Name</label>
                                                <input class="form-control form-control-lg" type="text" id="middlename"
                                                    name="middlename"
                                                    value="<?= isset($profile) ? $profile->middlename : '' ?>">
                                                <span class="text-danger error-text middlename_error"></span>
                                            </div>
                                            <div class="form-group mb-4">
                                                <label>Last Name</label>
                                                <input class="form-control form-control-lg" type="text" id="lastname"
                                                    name="lastname"
                                                    value="<?= isset($profile) ? $profile->lastname : '' ?>">
                                                <span class="text-danger error-text lastname_error"></span>
                                            </div>
                                            <div class="form-group mt-4 mb-4">
                                                <label>Gender</label>
                                                <div class="d-flex">
                                                    <div class="custom-control custom-radio mb-5 mr-20">
                                                        <input type="radio" id="customRadioMale" name="gender"
                                                            class="custom-control-input" value="M" <?= isset($profile) && $profile->gender == 'M' ? 'checked="checked"' : '' ?>>
                                                        <label class="custom-control-label weight-400"
                                                            for="customRadioMale">Male</label>
                                                    </div>
                                                    <div class="custom-control custom-radio mb-5">
                                                        <input type="radio" id="customRadioFemale" name="gender"
                                                            class="custom-control-input" value="F" <?= isset($profile) && $profile->gender == 'F' ? 'checked="checked"' : '' ?>>
                                                        <label class="custom-control-label weight-400"
                                                            for="customRadioFemale">Female</label>
                                                    </div>
                                                </div>
                                                <span class="text-danger error-text gender_error"></span>
                                            </div>
                                            <div class="form-group mt-4">
                                                <label class="mb-3">Date of birth</label>
                                                <input class="form-control form-control-lg" type="date" id="dateofbirth"
                                                    name="dateofbirth"
                                                    value="<?= isset($profile) ? $profile->dateofbirth : '' ?>">
                                                <span class="text-danger error-text dateofbirth_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Religion</label>
                                                <input class="form-control form-control-lg" type="text" id="religion"
                                                    name="religion"
                                                    value="<?= isset($profile) ? $profile->religion : '' ?>">
                                                <span class="text-danger error-text religion_error"></span>
                                            </div>
                                        </li>
                                        <li class="weight-500 col-md-6">

                                            <div class="form-group">
                                                <label>Street</label>
                                                <input class="form-control form-control-lg" type="text" id="street"
                                                    name="street"
                                                    value="<?= isset($profile) ? $profile->street : '' ?>">
                                                <span class="text-danger error-text street_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Barangay</label>

                                                <div class="input-group">
                                                    <input class="form-control" type="text" id="barangay"
                                                        name="barangay" readonly>

                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary" id="btnBarangay">
                                                            <i class="fas fa-search"></i> Select
                                                        </button>
                                                    </div>
                                                </div>

                                                <span class="text-danger error-text barangay_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Municipality</label>
                                                <input class="form-control form-control-lg" type="text"
                                                    id="municipality" name="municipality" value="">
                                                <span class="text-danger error-text municipality_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Province</label>
                                                <input class="form-control form-control-lg" type="text" id="province"
                                                    name="province" value="">
                                                <span class="text-danger error-text province_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                <input class="form-control form-control-lg" type="text"
                                                    id="studentcontact" name="studentcontact"
                                                    value="<?= isset($profile) ? $profile->contact : '' ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Remarks</label>
                                                <input class="form-control form-control-lg" type="text" id="remarks"
                                                    name="remarks" value="">
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Basic Tab End -->
                            <!-- Other Related Tab start -->
                            <div class="tab-pane fade" id="otherrelated" role="tabpanel">
                                <div class="pd-20">
                                    <ul class="profile-edit-list row">
                                        <li class="weight-500 col-md-6">

                                            <div class="form-group">
                                                <label>Father's Name</label>
                                                <input class="form-control form-control-lg" type="text" id="fathersname"
                                                    name="fathersname"
                                                    value="<?= isset($student) ? $student->fathersname : '' ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Guardian</label>
                                                <input class="form-control form-control-lg" type="text" id="guardian"
                                                    name="guardian"
                                                    value="<?= isset($student) ? $student->guardiansname : '' ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Relationship</label>
                                                <input class="form-control form-control-lg" type="text"
                                                    id="guardianrelationship" name="guardianrelationship"
                                                    value="<?= isset($student) ? $student->relationship : '' ?>">
                                            </div>
                                        </li>
                                        <li class="weight-500 col-md-6">

                                            <div class="form-group">
                                                <label>Mother's Name</label>
                                                <input class="form-control form-control-lg" type="text" id="mothersname"
                                                    name="mothersname"
                                                    value="<?= isset($student) ? $student->mothersname : '' ?>">
                                            </div>

                                            <div class="form-group">
                                                <label>Parent/Guardian Contact</label>
                                                <input class="form-control form-control-lg" type="text"
                                                    id="parentcontact" name="parentcontact"
                                                    value="<?= isset($student) ? $student->parent_guardian_contact : '' ?>">
                                            </div>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Other Related Tab End -->
                            <!-- Social Media Tab start -->
                            <div class="tab-pane fade height-100-p" id="socialmedia" role="tabpanel">
                                <div class="profile-setting">
                                    <ul class="profile-edit-list row">
                                        <li class="weight-500 col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input class="form-control form-control-lg" type="text" id="email"
                                                    name="email" placeholder="Paste your email here"
                                                    value="<?= isset($profile) ? $profile->email : '' ?>">
                                            </div>


                                        </li>
                                        <li class="weight-500 col-md-6">

                                            <div class="form-group">
                                                <label>Facebook URL:</label>
                                                <input class="form-control form-control-lg" type="text" id="facebookurl"
                                                    name="facebookurl" placeholder="Paste your link here"
                                                    value="<?= isset($profile) ? $profile->fbaccount : '' ?>">
                                            </div>

                                        </li>
                                    </ul>

                                </div>
                            </div>
                            <!-- Social Media Tab End -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>


    $('#post-student-information-form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formData,
            processData: false,
            dataType: 'json',
            contentType: false,
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {

                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        toastr.success(response.msg);
                        $(form)[0].reset();
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

    $('#profile_file').ijaboCropTool({
        preview: '.ci-avatar-photo',
        setRatio: 1,

        allowedExtensions: ['jpg', 'jpeg', 'png'],
        processUrl: '<?= route_to('update-student-profile-picture') ?>?id=<?= isset($profile) ? $profile->id : 0 ?>',
        withCSRF: ['<?= csrf_token() ?>', '<?= csrf_hash() ?>'],
        onSuccess: function (message, element, status) {
            if (status == 1) {
                toastr.success(message);
            } else {
                toastr.error(message);
            }
        },
        onError: function (message, element, status) {
            alert(message);
        }
    });



</script>
<?= $this->endSection() ?>