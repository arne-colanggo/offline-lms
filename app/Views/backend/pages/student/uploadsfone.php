<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Upload Students From SF1</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Upload SF1
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">

        </div>
    </div>
</div>


<form action="<?= route_to('post.upload.sf1') ?>" method="post" id="enrol_sf1_form">
    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
    <div class="row">
        <div class="col-md-9">
            <div class="card-box mb-2">
                <div class="card-header">
                    <div class="clearfix">
                        <div class="pull-left">
                            List of Students
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless table-hover table-striped" id="sf1_table">
                            <thead>
                                <tr>
                                    <th scope="col">LRN</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Gender</th>
                                    <th scope="col">Date of Birth</th>
                                    <th scope="col">Age</th>
                                    <th scope="col">Mother Tongue</th>
                                    <th scope="col">Ethnic</th>
                                    <th scope="col">Religion</th>
                                    <th scope="col">Street</th>
                                    <th scope="col">Barangay</th>
                                    <th scope="col">Municipality</th>
                                    <th scope="col">Province</th>
                                    <th scope="col">Father</th>
                                    <th scope="col">Mother</th>
                                    <th scope="col">Guardian</th>
                                    <th scope="col">Relationship</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Learning Modality</th>
                                    <th scope="col">Remarks</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-box mb-2">
                <div class="card-body">
                    <div class="form-group">
                        <label for="">Select SF1</label>
                        <input class="form-control-file form-control" type="file" id="sf1_file" name="sf1_file"
                            height="auto" accept=".xls,.xlsx">
                        <span class="text-danger error-text sf1_file_error"></span>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-primary">Upload</button>
    </div>
</form>

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
<script src="/extra-assets/excelreader/xlsx.full.min.js"></script>


<script>
    var data = [];
    document.getElementById("sf1_file").onchange = evt => {
        // (A) NEW FILE READER
        var reader = new FileReader();

        // (B) ON FINISH LOADING
        reader.addEventListener("loadend", evt => {
            // (B1) GET THE FIRST WORKSHEET
            var workbook = XLSX.read(evt.target.result, { type: "binary" }),
                worksheet = workbook.Sheets[workbook.SheetNames[0]],
                range = XLSX.utils.decode_range(worksheet["!ref"]);

            // (B2) READ CELLS IN ARRAY

            for (let row = range.s.r; row <= range.e.r; row++) {

                let lrn = worksheet[XLSX.utils.encode_cell({ r: row, c: 0 })]
                if (row > 6 && lrn) {

                    if (lrn.v.toString().length == 12) {

                        let i = data.length;
                        data.push([]);

                        for (let col = range.s.c; col <= range.e.c; col++) {
                            let row_to_be_discarded;
                            let cell = worksheet[XLSX.utils.encode_cell({ r: row, c: col })];
                            if (cell) {

                                data[i].push(cell.v);

                            }

                        }
                    }
                }
            }

            $('#sf1_table').DataTable({
                data: data,
                columns: [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 },
                    { data: 5 },
                    { data: 6 },
                    { data: 7 },
                    { data: 8 },
                    { data: 9 },
                    { data: 10 },
                    { data: 11 },
                    { data: 12 },
                    { data: 13 },
                    { data: 14 },
                    { data: 15 },
                    { data: 16 },
                    { data: 17 },
                    { data: 18 },
                ],

            });
        });

        // (C) START - READ SELECTED EXCEL FILE
        reader.readAsArrayBuffer(evt.target.files[0]);


    };

    $('#enrol_sf1_form').on('submit', function (e) {
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var form = this;
        var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);
        formdata.append('data', JSON.stringify(data));

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
                        console.log(response.msg);
                        toastr.success(response.msg);


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

</script>

<?= $this->endSection() ?>