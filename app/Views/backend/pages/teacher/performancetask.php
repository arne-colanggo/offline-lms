<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="card-box mb-30">
    <div class="pd-20 d-flex justify-content-between align-items-center flex-wrap">
        <h4 class="text-blue h4 mb-0">Performance Tasks</h4>
        <div class="d-flex">
            <form method="get" class="mr-2">
                <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">All classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= esc($c['label']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </form>
            <a href="<?= route_to('get.rubrics') ?><?= $classId ? '?class_id=' . $classId : '' ?>"
                class="btn btn-outline-primary btn-sm mr-2">Rubrics</a>
            <a href="<?= route_to('get.performancetask.new') ?><?= $classId ? '?class_id=' . $classId : '' ?>"
                class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> New Task</a>
        </div>
    </div>
    <div class="pb-20 px-3">
        <table class="data-table table stripe hover nowrap" id="taskTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Term</th>
                    <th>Rubric</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Scored</th>
                    <th class="datatable-nosort">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><?= esc($t['title']) ?></td>
                        <td><?= esc($t['class_name']) ?></td>
                        <td><?= $t['term'] ?></td>
                        <td><?= esc($t['rubric_name']) ?></td>
                        <td><?= $t['due_date'] ? date('M d, Y', strtotime($t['due_date'])) : '-' ?></td>
                        <td><?= $t['is_published'] ? '<span class="badge badge-success">Published</span>' : '<span class="badge badge-secondary">Draft</span>' ?>
                        </td>
                        <td><?= $t['scored_count'] ?></td>
                        <td>
                            <a href="<?= route_to('get.performancetask.score', $t['id']) ?>"
                                class="btn btn-sm btn-success">Score</a>
                            <a href="<?= route_to('get.performancetask.edit', $t['id']) ?>"
                                class="btn btn-sm btn-outline-primary">Edit</a>
                            <button class="btn btn-sm btn-outline-danger btn-del" data-id="<?= $t['id'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
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
    $(function () {
        if ($.fn.DataTable) { $('#taskTable').DataTable({ responsive: true }); }
        $('.btn-del').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this task?', text: 'All student scores for it will be deleted too.', icon: 'warning', showCancelButton: true }).then(res => {
                if (!res.isConfirmed) return;
                $.post('<?= base_url('admin/performance-task') ?>/' + id + '/delete', function (r) {
                    r.status ? (toastr.success(r.message), setTimeout(() => location.reload(), 600)) : toastr.error(r.message);
                }, 'json');
            });
        });
    });
</script>
<?= $this->endSection() ?>