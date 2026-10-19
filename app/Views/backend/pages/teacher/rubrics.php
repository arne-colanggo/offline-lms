<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="card-box mb-30">
    <div class="pd-20 d-flex justify-content-between align-items-center">
        <h4 class="text-blue h4 mb-0">Rubrics</h4>
        <a href="<?= route_to('get.rubric.new') ?><?= $classId ? '?class_id=' . $classId : '' ?>"
            class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New
            Rubric</a>
    </div>
    <div class="pb-20 px-3">
        <table class="data-table table stripe hover nowrap" id="rubricTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Criteria</th>
                    <th>Used by tasks</th>
                    <th class="datatable-nosort">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rubrics as $r): ?>
                    <tr>
                        <td><?= esc($r['name']) ?></td>
                        <td><?= esc($r['description']) ?></td>
                        <td><?= $r['criteria_count'] ?></td>
                        <td><?= $r['used_count'] ?></td>
                        <td>
                            <a href="<?= route_to('get.rubric.edit', $r['id']) ?>"
                                class="btn btn-sm btn-outline-primary">Edit</a>
                            <?php if (!$r['used_count']): ?>
                                <button class="btn btn-sm btn-outline-danger btn-del" data-id="<?= $r['id'] ?>">Delete</button>
                            <?php endif ?>
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
        if ($.fn.DataTable) { $('#rubricTable').DataTable({ responsive: true }); }
        $('.btn-del').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this rubric?', icon: 'warning', showCancelButton: true }).then(res => {
                if (!res.isConfirmed) return;
                $.post('<?= base_url('admin/rubric') ?>/' + id + '/delete', function (r) {
                    r.status ? (toastr.success(r.message), setTimeout(() => location.reload(), 600)) : toastr.error(r.message);
                }, 'json');
            });
        });
    });
</script>
<?= $this->endSection() ?>