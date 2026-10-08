<?= $this->extend('backend/layout/pages-layout') ?>

<style>
    #sf1-loading-overlay {
        position: fixed;
        z-index: 99999;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;

        /* Center the loading box */
        display: flex;
        align-items: center;
        justify-content: center;

        background: rgba(0, 0, 0, 0.45);
    }

    .sf1-loading-content {
        width: 320px;
        padding: 30px;
        text-align: center;

        background: #ffffff;
        border-radius: 10px;

        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.25);
    }

    .sf1-loading-content .spinner-border {
        width: 3.5rem;
        height: 3.5rem;
    }

    .sf1-loading-content strong {
        display: block;
        font-size: 18px;
        margin-top: 10px;
    }
</style>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Batch Enroll Students From SF1</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Batch Enroll Students from SF1
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">

        </div>
    </div>
</div>
<!-- Loading Spinner -->
<div id="sf1-loading-overlay" style="display: none;">
    <div class="sf1-loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Uploading...</span>
        </div>

        <div class="mt-3">
            <strong>Uploading SF1 Data...</strong>
        </div>

        <div class="text-muted small mt-1">
            Please wait while the system processes the students.
        </div>
    </div>
</div>

<form action="<?= route_to('post.batch.enroll.student.section') ?>" method="post" id="enrol_sf1_form">
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
                        <table class="table table-sm table-borderless table-hover table-striped" id="sf1_table"
                            style="width:100%">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                        <span class="text-danger error-text data_error"></span>
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
    var headers = [];

    // Zero-based row indexes of the header rows in the SF1 sheet (adjust if your template differs)
    const HEADER_ROW_START = 4;
    const HEADER_ROW_END = 6;
    const DATA_ROW_START = 7;   // first row that may contain a student

    // Get a cell value, resolving merged cells (merged cells only store a value in the top-left cell)
    function getCellValue(ws, r, c) {
        const addr = XLSX.utils.encode_cell({ r: r, c: c });
        if (ws[addr] && ws[addr].v !== undefined && ws[addr].v !== null) {
            return String(ws[addr].v).trim();
        }
        const merges = ws['!merges'] || [];
        for (const m of merges) {
            if (r >= m.s.r && r <= m.e.r && c >= m.s.c && c <= m.e.c) {
                const origin = ws[XLSX.utils.encode_cell({ r: m.s.r, c: m.s.c })];
                return origin && origin.v !== undefined ? String(origin.v).trim() : '';
            }
        }
        return '';
    }

    function buildHeaders(ws, totalCols) {
        const result = [];
        for (let c = 0; c < totalCols; c++) {
            const parts = [];
            for (let r = HEADER_ROW_START; r <= HEADER_ROW_END; r++) {
                const val = getCellValue(ws, r, c).replace(/\s+/g, ' ');
                if (val && !parts.includes(val)) parts.push(val);
            }
            result.push(parts.length ? parts.join(' - ') : 'Column ' + (c + 1));
        }
        return result;
    }

    function renderTable() {
        // Destroy old table and its header
        if ($.fn.DataTable.isDataTable('#sf1_table')) {
            $('#sf1_table').DataTable().clear().destroy();
        }
        $('#sf1_table thead').empty();
        $('#sf1_table tbody').empty();

        if (!data.length) return;

        // Build header dynamically
        let headRow = '<tr>';
        headers.forEach(h => {
            headRow += '<th scope="col">' + $('<div>').text(h).html() + '</th>';
        });
        headRow += '</tr>';
        $('#sf1_table thead').html(headRow);

        // Build columns dynamically
        const columns = headers.map((h, i) => ({
            data: i,
            defaultContent: ''
        }));

        $('#sf1_table').DataTable({
            data: data,
            columns: columns,
            scrollX: true
        });
    }

    document.getElementById("sf1_file").onchange = evt => {
        const file = evt.target.files[0];

        // Reset previous data every time a new file is chosen
        data = [];
        headers = [];
        renderTable();

        if (!file) return;

        const reader = new FileReader();

        reader.addEventListener("loadend", e => {
            const workbook = XLSX.read(e.target.result, { type: "array" });
            const worksheet = workbook.Sheets[workbook.SheetNames[0]];
            const range = XLSX.utils.decode_range(worksheet["!ref"]);

            const totalCols = range.e.c + 1;
            let lastUsedCol = -1;   // used to drop empty trailing columns

            // Read all student rows (keeps every column position, even empty cells)
            for (let row = DATA_ROW_START; row <= range.e.r; row++) {
                const lrnCell = worksheet[XLSX.utils.encode_cell({ r: row, c: 0 })];

                if (lrnCell && lrnCell.v.toString().length == 12) {
                    const rowData = [];

                    for (let col = 0; col < totalCols; col++) {
                        const cell = worksheet[XLSX.utils.encode_cell({ r: row, c: col })];
                        const value = (cell && cell.v !== undefined && cell.v !== null) ? cell.v : '';
                        rowData.push(value);

                        if (value !== '' && col > lastUsedCol) lastUsedCol = col;
                    }
                    data.push(rowData);
                }
            }

            // Also count columns that have a header, even if all their data is empty
            const allHeaders = buildHeaders(worksheet, totalCols);
            for (let c = totalCols - 1; c > lastUsedCol; c--) {
                const hasHeader = getCellValue(worksheet, HEADER_ROW_END, c) !== '' ||
                    getCellValue(worksheet, HEADER_ROW_START, c) !== '';
                if (hasHeader) { lastUsedCol = c; break; }
            }

            const finalCols = lastUsedCol + 1;
            headers = allHeaders.slice(0, finalCols);
            data = data.map(r => r.slice(0, finalCols));

            renderTable();
        });

        reader.readAsArrayBuffer(file);
    };

    $('#enrol_sf1_form').on('submit', function (e) {
        e.preventDefault();

        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        var form = this;

        var formdata = new FormData(form);

        formdata.append(csrfName, csrfHash);
        formdata.append('sectionid', <?= $sectionid ?>);
        formdata.append('headers', JSON.stringify(headers));   // NEW: column names from the Excel
        formdata.append('data', JSON.stringify(data));

        // Show loading spinner
        $('#sf1-loading-overlay').fadeIn(200);

        // Disable upload button
        $(form).find('button[type="submit"]')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-2"></span>Uploading...');

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
                $('.ci_csrf_data').val(response.token);

                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        $(form)[0].reset();

                        data = [];
                        headers = [];
                        renderTable();

                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, value) {
                        $(form).find('span.' + prefix + '_error').text(value);
                    });
                }
            },

            error: function (xhr, status, error) {
                console.error('SF1 Upload Error:', error);
                toastr.error('An error occurred while uploading the SF1 data. Please try again.');
            },

            complete: function () {
                $('#sf1-loading-overlay').fadeOut(200);

                $(form).find('button[type="submit"]')
                    .prop('disabled', false)
                    .html('Upload');
            }
        });
    });
</script>

<?= $this->endSection() ?>