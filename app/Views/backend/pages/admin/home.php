<?= $this->extend('backend/layout/pages-layout') ?>

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
<div class="row pb-10">
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>


    $(document).on('click', '.btn-class', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        window.location.href = '<?= route_to('grade-level-sections') . "/?id=" ?>' + id;
    })
</script>

<?= $this->endSection() ?>