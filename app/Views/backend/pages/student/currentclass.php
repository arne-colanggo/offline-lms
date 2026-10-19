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

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20 btn btn-active-quiz">


        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">Active</div>
                    <div class="font-14 text-secondary weight-500">
                        <a href="<?= route_to('active-exams') . "/?id=" . $class->id ?>"> Quiz/Exam </a>
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#00eccf" style="color: rgb(0, 236, 207);">
                        <i class="icon-copy fa fa-file-text" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20 btn_learning_resources btn">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">View</div>
                    <div class="font-14 text-secondary weight-500">
                        Learning Resources
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#ff5b5b" style="color: rgb(255, 91, 91);">
                        <i class="icon-copy fa fa-book" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20 btn">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">My</div>
                    <div class="font-14 text-secondary weight-500">
                        Progress Report
                    </div>
                </div>
                <div class="widget-icon">
                    <div class="icon">
                        <i class="icon-copy fa fa-line-chart" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20 btn-performance-task btn">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark">My</div>
                    <div class="font-14 text-secondary weight-500">Performance Task</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy fa fa-tasks" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20 btn-virtualclass btn">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><a href="">Classroom</a></div>
                    <div class="font-14 text-secondary weight-500">Virtual Classroom</div>
                </div>
                <div class="widget-icon">
                    <div class="icon" data-color="#09cc06" style="color: rgb(9, 204, 6);">
                        <i class="icon-copy fa fa-desktop" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).on('click', '.btn-performance-task', function (e) {
        e.preventDefault();
        window.location.href = '<?= route_to('view-my-performance-task') . "/?id=" . $class->id ?>';
    })

    $(document).on('click', '.btn-active-quiz', function (e) {
        e.preventDefault();
        window.location.href = '<?= route_to('student.quizzes') . "/?id=" . $class->id ?>';
    })

    $(document).on('click', '.btn-virtualclass', function (e) {
        e.preventDefault();
        window.location.href = '';
    });
    $(document).on('click', '.btn_learning_resources', function (e) {
        e.preventDefault();
        window.location.href = '<?= route_to("view-learning-resources") . "/?id=" . $class->id ?>';
    });
</script>

<?= $this->endSection() ?>