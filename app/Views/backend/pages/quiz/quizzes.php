<?php
$scope = $scope ?? null;   // class context when opened from a Class page
$builderUrl = fn($id) => $scope ? str_replace('{id}', $id, $scope['urls']['builderTpl']) : route_to('get.quiz.builder', $id);
$saveUrl = $scope ? $scope['urls']['quizSave'] : route_to('post.quiz');
$deleteUrl = $scope ? $scope['urls']['quizDelete'] : strstr(route_to('delete.quiz', 9999999), '9999999', true);
$bankUrl = $scope ? $scope['urls']['questionsPage'] : route_to('get.questions');
?>

<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4>
                    <?= $scope ? 'Quizzes: ' . esc($scope['class']['subject_name']) : 'Quizzes' ?>
                </h4>
                <?php if ($scope): ?>
                    <small class="text-muted">
                        <?= esc($scope['class']['section_name']) ?> &middot;
                        <?= esc($scope['class']['schoolyear_name']) ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 text-md-right">
            <?php if ($scope): ?>
                <a href="<?= esc($scope['backUrl']) ?>" class="btn btn-outline-secondary mr-2">Back to class</a>
            <?php endif; ?>
            <a href="<?= esc($bankUrl) ?>" class="btn btn-outline-primary mr-2">
                <?= $scope ? 'Questions' : 'Question bank' ?>
            </a>
            <button class="btn btn-primary" data-toggle="modal" data-target="#quizModal">New quiz</button>
        </div>
    </div>
</div>

<div class="card-box mb-30">
    <div class="pd-20 table-responsive">
        <table class="table table-striped w-100" id="tblQuizzes">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Section</th>
                    <th>School year</th>
                    <th>Term</th>
                    <th>Type</th>
                    <th>Questions</th>
                    <th>Status</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $q): ?>
                    <tr>
                        <td><strong>
                                <?= esc($q['quizcode']) ?>
                            </strong></td>
                        <td>
                            <?= esc($q['section']) ?>
                        </td>
                        <td>
                            <?= esc($q['schoolyear']) ?>
                        </td>
                        <td>
                            <?= (int) $q['term'] ?>
                        </td>
                        <td>
                            <?= esc($quizTypes[$q['quiz_type']] ?? $q['quiz_type']) ?>
                        </td>
                        <td>
                            <?= (int) $q['total_questions'] ?>
                        </td>
                        <td>
                            <?php if ($q['isactive'] === 'T'): ?>
                                <span class="badge badge-success">Open to students</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right text-nowrap">
                            <?php if ($scope): ?>
                                <a href="<?= esc(str_replace('{id}', $q['id'], $scope['urls']['monitorTpl'])) ?>"
                                    class="btn btn-sm btn-outline-success">Monitor</a>
                            <?php endif; ?>
                            <a href="<?= esc($builderUrl($q['id'])) ?>" class="btn btn-sm btn-outline-primary">Choose
                                questions</a>
                            <button class="btn btn-sm btn-outline-danger btn-del" data-id="<?= $q['id'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="quizModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New quiz</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!$scope): ?>
                    <div class="form-group">
                        <label>Section <span class="text-danger">*</span></label>
                        <select id="nSection" class="form-control">
                            <option value="">Select a section</option>
                            <?php foreach ($sections as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= esc($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="form-row">
                    <?php if (!$scope): ?>
                        <div class="form-group col-md-6">
                            <label>School year <span class="text-danger">*</span></label>
                            <select id="nSchoolYear" class="form-control">
                                <?php foreach ($schoolyears as $sy): ?>
                                    <option value="<?= $sy['id'] ?>">
                                        <?= esc($sy['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="form-group <?= $scope ? 'col-12' : 'col-md-6' ?>">
                        <label>Term <span class="text-danger">*</span></label>
                        <select id="nTerm" class="form-control">
                            <?php for ($t = 1; $t <= 4; $t++): ?>
                                <option value="<?= $t ?>">Term
                                    <?= $t ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Quiz type <span class="text-danger">*</span></label>
                    <select id="nType" class="form-control">
                        <?php foreach ($quizTypes as $k => $v): ?>
                            <option value="<?= $k ?>">
                                <?= esc($v) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnCreate">Create and choose questions</button>
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
    $(function () {
        // CSRF: send the current token on every request and pick up the regenerated one from each JSON response
        const CSRF = { value: '<?= csrf_hash() ?>' };
        $.ajaxSetup({ beforeSend: xhr => xhr.setRequestHeader('X-CSRF-TOKEN', CSRF.value) });
        $(document).ajaxComplete((e, xhr) => { if (xhr.responseJSON && xhr.responseJSON.token) CSRF.value = xhr.responseJSON.token; });

        $('#tblQuizzes').DataTable({ order: [], columnDefs: [{ targets: 'no-sort', orderable: false }] });

        $('#btnCreate').on('click', function () {
            const $b = $(this).prop('disabled', true);
            $.ajax({
                url: '<?= esc($saveUrl, 'js') ?>', type: 'POST', contentType: 'application/json',
                data: JSON.stringify({
                    sectionid: $('#nSection').val(),
                    schoolyearid: $('#nSchoolYear').val(),
                    term: $('#nTerm').val(),
                    quiz_type: $('#nType').val()
                })
            }).done(r => { window.location = r.redirect; })
                .fail(x => { toastr.error((x.responseJSON || {}).message || 'Could not create the quiz.'); $b.prop('disabled', false); });
        });

        $('#tblQuizzes').on('click', '.btn-del', function () {
            const id = $(this).data('id');
            swal({
                title: 'Delete this quiz?', text: 'The questions stay in the question bank.', type: 'warning', showCancelButton: true, confirmButtonText: 'Delete'
            })
                .then(res => {
                    if (res.isConfirmed) return;
                    $.post('<?= esc($deleteUrl, 'js') ?>' + id)
                        .done(r => { toastr.success(r.message); setTimeout(() => location.reload(), 600); })
                        .fail(x => toastr.error((x.responseJSON || {}).message || 'Could not delete the quiz.'));
                });
        });
    });
</script>
<?= $this->endSection() ?>