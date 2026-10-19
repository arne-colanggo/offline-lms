<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4><?= esc($bow->grade_name) ?> - <?= esc($bow->subject_name) ?> (Q<?= (int) $bow->term ?>)</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route_to('admin.home') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= route_to('budgetofwork') ?>">Budget of Work</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc($bow->name) ?></li>
                </ol>
            </nav>
        </div>
        <?php if ($canManage): ?>
        <div class="col-md-4 col-sm-12 text-right">
            <button class="btn btn-primary btn-sm" id="btn_new_detail">
                <i class="bi bi-plus-circle"></i> Add Competency
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">

<!-- Summary cards -->
<?php
    $weeks = count(array_unique(array_map(fn($d) => $d->week, $details)));
    $files = 0;
    foreach ($resources as $list) { $files += count($list); }
?>
<div class="row pb-10">
    <div class="col-md-4 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= count($details) ?></div>
                    <div class="font-14 text-secondary weight-500">Competencies</div>
                </div>
                <div class="widget-icon"><div class="icon" style="color:#00eccf"><i class="icon-copy bi bi-list-check"></i></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= $weeks ?></div>
                    <div class="font-14 text-secondary weight-500">Weeks Covered</div>
                </div>
                <div class="widget-icon"><div class="icon" style="color:#28a745"><i class="icon-copy bi bi-calendar-week"></i></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-20">
        <div class="card-box height-100-p widget-style3">
            <div class="d-flex flex-wrap">
                <div class="widget-data">
                    <div class="weight-700 font-24 text-dark"><?= $files ?></div>
                    <div class="font-14 text-secondary weight-500">Learning Resources</div>
                </div>
                <div class="widget-icon"><div class="icon" style="color:#ffc107"><i class="icon-copy bi bi-paperclip"></i></div></div>
            </div>
        </div>
    </div>
</div>

<div class="card-box mb-30">
    <div class="pd-20"><h5 class="h5 mb-0"><?= esc($bow->name) ?></h5></div>
    <div class="pb-20 px-3">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover" id="detail_table" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:70px">Week</th>
                        <th style="width:100px">Competency Code</th>
                        <th>Learning Objectives</th>
                        <th style="width:360px">Learning Resources</th>
                        <th class="text-right" style="width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($details as $d): ?>
                    <tr>
                        <td data-order="<?= (int) $d->week ?>">Week <?= (int) $d->week ?></td>
                        <td><?= esc($d->competencycode) ?></td>
                        <td><?= nl2br(esc($d->learning_objectives)) ?></td>
                        <td>
                            <?php foreach ($resources[$d->id] ?? [] as $r): ?>
                                <div class="mb-1">
                                    <span class="badge <?= $r->resourcestype === 'LP' ? 'badge-primary' : 'badge-secondary' ?>"
                                        title="<?= $r->resourcestype === 'LP' ? 'Learning Plan' : 'Learning Resource' ?>">
                                        <?= esc($r->resourcestype) ?>
                                    </span>
                                    <a href="<?= route_to('bow.resource.download') . '/?id=' . $r->id ?>">
                                        <i class="bi bi-download"></i> <?= strtoupper(esc($r->filetype)) ?> file [<?= strtoupper(esc($r->documentname)) ?>]
                                    </a>
                                    <?php if ($canManage): ?>
                                        <a href="#" class="text-danger ml-1 btn-delete-res" data-id="<?= $r->id ?>" title="Remove">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($canManage): ?>
                                <button class="btn btn-xs btn-link p-0 btn-add-res" data-detail="<?= $d->id ?>">
                                    <i class="bi bi-plus"></i> Add file
                                </button>
                            <?php endif; ?>
                        </td>
                                
                        <td class="text-right">
                            <?php if ($canManage): ?>
                            <button class="btn btn-sm btn-outline-secondary btn-edit-detail"
                                data-id="<?= $d->id ?>" data-week="<?= (int) $d->week ?>"
                                data-code="<?= esc($d->competencycode, 'attr') ?>"
                                data-objectives="<?= esc($d->learning_objectives, 'attr') ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-detail" data-id="<?= $d->id ?>">
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

<!-- Competency modal -->
<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form class="modal-content ajax-form" method="post" action="<?= route_to('post.bow.detail') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
            <input type="hidden" name="id" id="detail_id">
            <input type="hidden" name="bowid" value="<?= $bow->id ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="detail_modal_title">Add Competency</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Week</label>
                        <input type="number" min="1" max="52" class="form-control" name="week" id="detail_week">
                        <span class="text-danger error-text week_error"></span>
                    </div>
                    <div class="form-group col-md-9">
                        <label>Competency Code</label>
                        <input type="text" class="form-control" name="competencycode" id="detail_code" maxlength="50">
                        <span class="text-danger error-text competencycode_error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Learning Objectives</label>
                    <textarea class="form-control" rows="5" name="learning_objectives" id="detail_obj"></textarea>
                    <span class="text-danger error-text learning_objectives_error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Resource upload modal -->
<div class="modal fade" id="res_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content ajax-form" method="post" enctype="multipart/form-data"
            action="<?= route_to('post.bow.resource') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
            <input type="hidden" name="bowdetailsid" id="res_detail_id">
            <div class="modal-header">
                <h5 class="modal-title">Upload Learning Resource</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Type</label>
                    <select class="form-control" name="resourcestype">
                        <option value="LP">LP - Learning Plan</option>
                        <option value="LR">LR - Learning Resource</option>
                    </select>
                    <span class="text-danger error-text resourcestype_error"></span>
                </div>
                <div class="form-group">
                    <label>Document Name</label>
                    <input type="text" class="form-control" name="documentname" id="document name" placeholder="Type the name of the document">
                    <span class="text-danger error-text documentname_error"></span>
                </div>
                <div class="form-group">
                    <label>File <small class="text-muted">(PDF, Office, image, MP4 - max 10 MB)</small></label>
                    <input type="file" class="form-control-file form-control" name="resource_file">
                    <span class="text-danger error-text resource_file_error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload</button>
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
    $('#detail_table').DataTable({ order: [[0, 'asc']], pageLength: 25 });

    function csrfData(extra) {
        var $c = $('.ci_csrf_data').first();
        var d = Object.assign({}, extra || {});
        d[$c.attr('name')] = $c.val();
        return d;
    }

    function postJson(url, data) {
        $.post(url, csrfData(data), function (r) {
            $('.ci_csrf_data').val(r.token);
            toastr.remove();
            if (r.status == 1) { toastr.success(r.msg); setTimeout(() => location.reload(), 900); }
            else { toastr.error(r.msg); }
        }, 'json').fail(function () { toastr.error('An error occurred. Please try again.'); });
    }

    function confirmDelete(title, text, url, id) {
        Swal.fire({ title: title, text: text, icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete' })
            .then(function (res) { if (res.isConfirmed) postJson(url, { id: id }); });
    }

    // ---- competency modal ----
    $('#btn_new_detail').on('click', function () {
        $('#detail_modal form')[0].reset();
        $('#detail_id').val('');
        $('#detail_modal .error-text').text('');
        $('#detail_modal_title').text('Add Competency');
        $('#detail_modal').modal('show');
    });

    $(document).on('click', '.btn-edit-detail', function () {
        var $b = $(this);
        $('#detail_modal .error-text').text('');
        $('#detail_id').val($b.data('id'));
        $('#detail_week').val($b.data('week'));
        $('#detail_code').val($b.data('code'));
        $('#detail_obj').val($b.data('objectives'));
        $('#detail_modal_title').text('Edit Competency');
        $('#detail_modal').modal('show');
    });

    $(document).on('click', '.btn-delete-detail', function () {
        confirmDelete('Delete this competency?', 'Its uploaded files will be removed too.',
            '<?= route_to('delete.bow.detail') ?>', $(this).data('id'));
    });

    // ---- resources ----
    $(document).on('click', '.btn-add-res', function () {
        $('#res_modal form')[0].reset();
        $('#res_modal .error-text').text('');
        $('#res_detail_id').val($(this).data('detail'));
        $('#res_modal').modal('show');
    });

    $(document).on('click', '.btn-delete-res', function (e) {
        e.preventDefault();
        confirmDelete('Remove this file?', 'This cannot be undone.',
            '<?= route_to('delete.bow.resource') ?>', $(this).data('id'));
    });

    // ---- generic ajax form (handles file uploads too) ----
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
</script>
<?= $this->endSection() ?>