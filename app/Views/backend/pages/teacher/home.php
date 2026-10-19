<?= $this->extend('backend/layout/pages-teacher-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-7 col-sm-12">
            <div class="title">
                <h4>Dashboard</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route_to('teacher.home') ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-5 col-sm-12 text-right">

        </div>
    </div>
</div>

<!-- Summary cards -->
<div class="row pb-10">

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a
                            href="<?= route_to('section.dashboard') . '/?id=' . $advisory[0]->id ?>">
                            <?= $advisory[0]->name ?>
                        </a></div>
                    <div class="font-14 text-secondary weight-500">Class Advisory</div>

                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#00eccf"><i class="icon-copy bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= $nonadvisory[0]->totalclass ?></div>
                    <div class="font-14 text-secondary weight-500">Non-Advisory Classes</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" style="color:#1b00ff"><i class="icon-copy bi bi-layers"></i></div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- Announcement modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" id="announcement_form" method="post" action="<?= route_to('post.announcement') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
            <div class="modal-header">
                <h5 class="modal-title">New Announcement</h5>
                <button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" maxlength="150">
                    <span class="text-danger error-text title_error"></span>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" name="body" rows="4"></textarea>
                    <span class="text-danger error-text body_error"></span>
                </div>
                <div class="form-group">
                    <label>Audience</label>
                    <select class="form-control" name="audience">
                        <option value="All">Everyone</option>
                        <option value="Teachers">Teachers</option>
                        <option value="Students">Students</option>
                        <option value="Parents">Parents</option>
                    </select>
                    <span class="text-danger error-text audience_error"></span>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_pinned" name="is_pinned" value="1">
                    <label class="custom-control-label" for="is_pinned">Pin to top</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/scripts/chart.umd.min.js"></script>


<script>

</script>
<?= $this->endSection() ?>