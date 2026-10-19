<?= $this->extend('backend/layout/pages-student-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">

        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Dashboard</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route_to('student.home') ?>">Home</a></li>
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

<div class="row pb-10">

    <?php foreach ($classes as $key => $value): ?>
        <div class="col-xl-3 col-lg-3 col-md-6 mb-20 btn btn-class" data-id="<?= $value->id ?>">
            <div class="card-box height-100-p widget-style3">
                <div class="d-flex flex-wrap">
                    <div class="widget-data">
                        <div class="weight-700 font-24 text-dark">
                            <?= $value->sectionname ?>
                        </div>
                        <div class="font-14 text-secondary weight-500">
                            <?= $value->classname ?>
                        </div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#00eccf" style="color: rgb(0, 236, 207);">
                            <i class="icon-copy bi bi-window-fullscreen"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).on('click', '.btn-class', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        window.location.href = '<?= route_to('get-current-class') . "/?id=" ?>' + id;
    })
</script>
<?= $this->endSection() ?>