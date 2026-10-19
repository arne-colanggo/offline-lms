<<?= $this->extend('backend/layout/pages-student-layout') ?>

    <?= $this->section('content') ?>
    <div class="page-header">
        <div class="row">

            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Dashboard</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Dashboard
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card-box">
                <div class="card-header">
                    <div class="clearfix">
                        <div class="pull-left">
                            Active Exams
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless table-hover table-striped" id="exams_table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Exam Code</th>
                                <th scope="col">Quarter</th>
                                <th scope="col">Score</th>
                                <th scope="col">Duration(mins)</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


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

        var exams_DT = $('#exams_table').DataTable({
            processing: true,
            serverSide: true,
            resposive: true,
            autowidth: true,
            scrollY: "300px",
            scrollX: true,
            scrollCollapse: true,
            ajax: "<?= route_to('get-active-exams') . "/?id=" . $class->id; ?>",
            dom: "Brtip",
            info: true,
            fnCreatedRow: function (row, data, index) {
                $('td', row).eq(0).html(index + 1);

            },
            columnDefs: [
                { ordering: false, targets: [0, 1, 2, 3] },
            ],
        });

    </script>
    <?= $this->endSection() ?>