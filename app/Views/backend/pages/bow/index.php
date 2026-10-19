<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title"><h4>Budget of Work</h4></div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route_to('admin.home') ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Budget of Work</li>
                </ol>
            </nav>
        </div>
        <?php if ($canManage): ?>
        <div class="col-md-4 col-sm-12 text-right">
            <button class="btn btn-primary btn-sm" id="btn_new_bow">
                <i class="bi bi-plus-circle"></i> New BOW
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">

<div class="card-box mb-30">
    <div class="pd-20"><h5 class="h5 mb-0">Budget of Work List</h5></div>
    <div class="pb-20 px-3">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover" id="bow_table" style="width:100%">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Subject</th>
                        <th>Quarter</th>
                        <th>Title</th>
                        <th>Competencies</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bows as $b): ?>
                    <tr>
                        <td><?= esc($b->grade_name) ?></td>
                        <td><?= esc($b->subject_name) ?></td>
                        <td>Q<?= (int) $b->term ?></td>
                        <td><?= esc($b->name) ?></td>
                        <td><span class="badge badge-info"><?= (int) $b->competencies ?></span></td>
                        <td class="text-right">
                            <a href="<?= route_to('bow.view') . '/?id=' . $b->subjectid ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-folder2-open"></i> Open
                            </a>
                            <?php if ($canManage): ?>
                            <button class="btn btn-sm btn-outline-secondary btn-edit-bow"
                                data-id="<?= $b->id ?>" data-subjectid="<?= $b->subjectid ?>"
                                data-term="<?= $b->term ?>" data-name="<?= esc($b->name, 'attr') ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-bow" data-id="<?= $b->id ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- BOW modal -->
<div class="modal fade" id="bow_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content ajax-form" method="post" action="<?= route_to('post.bow') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
            <input type="hidden" name="id" id="bow_id">
            <div class="modal-header">
                <h5 class="modal-title" id="bow_modal_title">New Budget of Work</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Subject</label>
                    <select class="form-control" name="subjectid" id="bow_subjectid">
                        <option value="">-- Select subject --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s->id ?>"><?= esc($s->grade_name) ?> - <?= esc($s->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="text-danger error-text subjectid_error"></span>
                </div>
                <div class="form-group">
                    <label>Quarter</label>
                    <select class="form-control" name="term" id="bow_term">
                        <option value="">-- Select Term --</option>
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <option value="<?= $i ?>">Term <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="text-danger error-text term_error"></span>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="name" id="bow_name" maxlength="150"
                        placeholder="e.g. Filipino 7 - Budget of Work, Q1">
                    <span class="text-danger error-text name_error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
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
<script>
    $('#bow_table').DataTable({ pageLength: 25 });

    // ---------- helpers ----------
    function csrfData(extra) {
        var $c = $('.ci_csrf_data').first();
        var d = Object.assign({}, extra || {});
        d[$c.attr('name')] = $c.val();
        return d;
    }

    function postJson(url, data, done) {
        $.post(url, csrfData(data), function (r) {
            $('.ci_csrf_data').val(r.token);
            toastr.remove();
            if (r.status == 1) { toastr.success(r.msg); setTimeout(() => location.reload(), 900); }
            else { toastr.error(r.msg); }
            if (done) done(r);
        }, 'json').fail(function () { toastr.error('An error occurred. Please try again.'); });
    }

    // ---------- modal ----------
    $('#btn_new_bow').on('click', function () {
        $('#bow_modal form')[0].reset();
        $('#bow_id').val('');
        $('#bow_modal .error-text').text('');
        $('#bow_modal_title').text('New Budget of Work');
        $('#bow_modal').modal('show');
    });

    $(document).on('click', '.btn-edit-bow', function () {
        var $b = $(this);
        $('#bow_modal .error-text').text('');
        $('#bow_id').val($b.data('id'));
        $('#bow_subjectid').val($b.data('subjectid'));
        $('#bow_term').val($b.data('term'));
        $('#bow_name').val($b.data('name'));
        $('#bow_modal_title').text('Edit Budget of Work');
        $('#bow_modal').modal('show');
    });

    // ---------- save ----------
    $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();
        var form = this;
        var fd = new FormData(form);
        $(form).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: $(form).attr('action'), type: 'POST', data: fd,
            processData: false, contentType: false, dataType: 'json',
            beforeSend: function () { toastr.remove(); $(form).find('span.error-text').text(''); },
            success: function (r) {
                $('.ci_csrf_data').val(r.token);
                if ($.isEmptyObject(r.error)) {
                    if (r.status == 1) { toastr.success(r.msg); setTimeout(() => location.reload(), 900); }
                    else { toastr.error(r.msg); }
                } else {
                    $.each(r.error, function (k, v) { $(form).find('span.' + k + '_error').text(v); });
                }
            },
            error: function () { toastr.error('An error occurred. Please try again.'); },
            complete: function () { $(form).find('button[type="submit"]').prop('disabled', false); }
        });
    });

    // ---------- delete ----------
    $(document).on('click', '.btn-delete-bow', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete this BOW?',
            text: 'All its competencies and uploaded files will be removed.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'
        }).then(function (res) {
            if (res.isConfirmed) postJson('<?= route_to('delete.bow') ?>', { id: id });
        });
    });
</script>
<?= $this->endSection() ?>