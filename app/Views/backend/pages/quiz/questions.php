<?php
// $scope is null for the admin-wide Question Bank, or the class context when opened from a Class page
$scope = $scope ?? null;
$urls = $scope['urls'] ?? [
    'list' => route_to('get.questions.list'),
    'get' => strstr(route_to('get.question', 9999999), '9999999', true),
    'save' => route_to('post.question'),
    'remove' => strstr(route_to('delete.question', 9999999), '9999999', true),
    'quizzes' => route_to('get.quizzes'),
];
?>
<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('stylesheets') ?>
<!-- Adjust these two paths to wherever your template keeps DataTables -->
<link rel="stylesheet" href="/backend/vendors/plugins/datatables/css/dataTables.bootstrap4.min.css">
<style>
    .q-text {
        max-width: 420px;
        white-space: normal;
    }

    .opt-hint {
        font-size: 13px;
        color: #6c757d;
    }

    #optionsWrap .opt-row .form-control[readonly] {
        background: #f7f8fa;
    }

    .pair-arrow {
        display: flex;
        align-items: center;
        padding: 0 .5rem;
        color: #6c757d;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4>
                    <?= $scope ? 'Questions: ' . esc($scope['class']['subject_name']) : 'Question Bank' ?>
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
            <a href="<?= esc($urls['quizzes']) ?>" class="btn btn-outline-primary mr-2">Quizzes</a>
            <button class="btn btn-primary" id="btnAdd"><i class="icon-copy ion-plus-circled"></i> Question</button>
        </div>
    </div>
</div>

<div class="card-box mb-30">
    <div class="pd-20">
        <div class="form-row">
            <div class="form-group col-lg-6">
                <label>Competency</label>
                <select id="fCompetency" class="form-control">
                    <option value="">All competencies</option>
                    <?php $grp = null;
                    foreach ($competencies as $c):
                        $label = ($c['subject'] ?? 'No subject') . ' - Term ' . $c['term'];
                        if ($label !== $grp):
                            if ($grp !== null)
                                echo '</optgroup>';
                            $grp = $label; ?>
                            <optgroup label="<?= esc($label) ?>">
                            <?php endif; ?>
                            <option value="<?= $c['id'] ?>">
                                <?= esc($c['competencycode']) ?> (Week
                                <?= esc($c['week']) ?>)
                            </option>
                        <?php endforeach;
                    if ($grp !== null)
                        echo '</optgroup>'; ?>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label>Question type</label>
                <select id="fType" class="form-control">
                    <option value="">All types</option>
                    <?php foreach ($optionTypes as $k => $v): ?>
                        <option value="<?= $k ?>">
                            <?= esc($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label>Taxonomy level</label>
                <select id="fTax" class="form-control">
                    <option value="">All levels</option>
                    <?php foreach ($taxonomy as $k => $v): ?>
                        <option value="<?= $k ?>">
                            <?= esc($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="pb-20 px-3">
        <table class="table table-striped w-100" id="tblQuestions">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Competency</th>
                    <th>Type</th>
                    <th>Level</th>
                    <th>In quizzes</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add / edit modal -->
<div class="modal fade" id="questionModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qModalTitle">Add question</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qId" value="">
                <div class="form-group">
                    <label>Competency <span class="text-danger">*</span></label>
                    <select id="qCompetency" class="form-control">
                        <option value="">Select the competency this question is based on</option>
                        <?php $grp = null;
                        foreach ($competencies as $c):
                            $label = ($c['subject'] ?? 'No subject') . ' - Term ' . $c['term'];
                            if ($label !== $grp):
                                if ($grp !== null)
                                    echo '</optgroup>';
                                $grp = $label; ?>
                                <optgroup label="<?= esc($label) ?>">
                                <?php endif; ?>
                                <option value="<?= $c['id'] ?>" title="<?= esc($c['learning_objectives']) ?>">
                                    <?= esc($c['competencycode']) ?> (Week
                                    <?= esc($c['week']) ?>)
                                </option>
                            <?php endforeach;
                        if ($grp !== null)
                            echo '</optgroup>'; ?>
                    </select>
                    <small class="form-text text-muted" id="qObjective"></small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Question type <span class="text-danger">*</span></label>
                        <select id="qOptionType" class="form-control">
                            <?php foreach ($optionTypes as $k => $v): ?>
                                <option value="<?= $k ?>">
                                    <?= esc($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Taxonomy level <span class="text-danger">*</span></label>
                        <select id="qTaxonomy" class="form-control">
                            <?php foreach ($taxonomy as $k => $v): ?>
                                <option value="<?= $k ?>">
                                    <?= esc($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Question <span class="text-danger">*</span></label>
                    <textarea id="qDescription" class="form-control" rows="3"></textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="mb-0" id="optionsLabel">Options</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">Add row</button>
                </div>
                <p class="opt-hint" id="optionsHint"></p>
                <div id="optionsWrap"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSave">Save question</button>
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

        const URL = <?= json_encode($urls) ?>;
        const OBJECTIVES = <?= json_encode(array_column($competencies, 'learning_objectives', 'id'), JSON_HEX_TAG | JSON_HEX_AMP) ?>;

        const HINTS = {
            MC: 'Add the choices and select the radio button beside the correct one.',
            TF: 'Select whether the statement is true or false.',
            ID: 'Add every answer you will accept (for example, spelling variants).',
            FB: 'Type ___ where each blank goes in the question, then enter one answer per blank, in order.',
            MT: 'Enter each item on the left and its correct match on the right.',
            ES: 'Essay questions have no options. The teacher scores the response manually.'
        };
        const LABELS = { MC: 'Choices', TF: 'Answer', ID: 'Accepted answers', FB: 'Answers (one per blank)', MT: 'Matching pairs', ES: 'Options' };

        const esc = s => $('<div>').text(s == null ? '' : s).html();

        /* ---------- DataTable ---------- */
        const table = $('#tblQuestions').DataTable({
            ajax: {
                url: URL.list,
                data: d => {
                    d.bowdetailsid = $('#fCompetency').val();
                    d.option_type = $('#fType').val();
                    d.question_type = $('#fTax').val();
                },
                dataSrc: 'data'
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'description', render: d => '<div class="q-text">' + esc(d.length > 160 ? d.slice(0, 160) + '...' : d) + '</div>' },
                { data: null, render: r => '<strong>' + esc(r.competencycode) + '</strong><br><small class="text-muted">' + esc(r.subject) + '</small>' },
                { data: 'option_label' },
                { data: 'taxonomy_label' },
                { data: 'used_in' },
                {
                    data: null, orderable: false, className: 'text-right text-nowrap', render: r => r.mine ?
                        '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' + r.id + '">Edit</button> ' +
                        '<button class="btn btn-sm btn-outline-danger btn-del" data-id="' + r.id + '">Delete</button>'
                        : '<span class="text-muted small">Added by a colleague</span>'
                }
            ]
        });
        $('#fCompetency, #fType, #fTax').on('change', () => table.ajax.reload());

        /* ---------- option rows ---------- */
        function rowHtml(type) {
            const rm = '<div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-rm" title="Remove">&times;</button></div>';
            const radio = '<div class="input-group-prepend"><div class="input-group-text"><input type="radio" name="correct" class="opt-correct" title="Correct answer"></div></div>';
            switch (type) {
                case 'MC': return '<div class="input-group mb-2 opt-row">' + radio + '<input type="text" class="form-control opt-desc" placeholder="Choice">' + rm + '</div>';
                case 'ID': return '<div class="input-group mb-2 opt-row"><input type="text" class="form-control opt-desc" placeholder="Accepted answer">' + rm + '</div>';
                case 'FB': return '<div class="input-group mb-2 opt-row"><div class="input-group-prepend"><span class="input-group-text blank-no"></span></div><input type="text" class="form-control opt-desc" placeholder="Answer">' + rm + '</div>';
                case 'MT': return '<div class="input-group mb-2 opt-row"><input type="text" class="form-control opt-desc" placeholder="Item"><span class="pair-arrow">matches</span><input type="text" class="form-control opt-match" placeholder="Match">' + rm + '</div>';
            }
            return '';
        }

        function renumber() {
            $('#optionsWrap .blank-no').each(function (i) { $(this).text('Blank ' + (i + 1)); });
        }

        function addRow(type, o) {
            const $r = $(rowHtml(type));
            if (o) {
                $r.find('.opt-desc').val(o.description || '');
                $r.find('.opt-match').val(o.match_text || '');
                if (o.isanswer === 'T') $r.find('.opt-correct').prop('checked', true);
            }
            $('#optionsWrap').append($r);
            renumber();
        }

        function renderOptions(type, opts) {
            const $w = $('#optionsWrap').empty();
            $('#optionsHint').text(HINTS[type]);
            $('#optionsLabel').text(LABELS[type]);
            $('#btnAddRow').toggle(['MC', 'ID', 'FB', 'MT'].includes(type));

            if (type === 'ES') return;

            if (type === 'TF') {
                ['True', 'False'].forEach(t => {
                    const found = (opts || []).find(o => o.description === t);
                    $w.append(
                        '<div class="input-group mb-2 opt-row"><div class="input-group-prepend"><div class="input-group-text">' +
                        '<input type="radio" name="correct" class="opt-correct" title="Correct answer"' + (found && found.isanswer === 'T' ? ' checked' : '') + '></div></div>' +
                        '<input type="text" class="form-control opt-desc" readonly value="' + t + '"></div>');
                });
                return;
            }

            const min = { MC: 4, ID: 1, FB: 1, MT: 3 }[type];
            const list = (opts && opts.length) ? opts : [];
            const n = Math.max(min, list.length);
            for (let i = 0; i < n; i++) addRow(type, list[i]);
        }

        $('#qOptionType').on('change', function () { renderOptions(this.value, null); });
        $('#btnAddRow').on('click', () => addRow($('#qOptionType').val()));
        $('#optionsWrap').on('click', '.btn-rm', function () {
            $(this).closest('.opt-row').remove();
            renumber();
        });
        $('#qCompetency').on('change', function () { $('#qObjective').text(OBJECTIVES[this.value] || ''); });

        function collectOptions() {
            const out = [];
            $('#optionsWrap .opt-row').each(function () {
                const $r = $(this), $c = $r.find('.opt-correct');
                out.push({
                    description: $.trim($r.find('.opt-desc').val()),
                    match_text: $.trim($r.find('.opt-match').val() || ''),
                    isanswer: $c.length ? $c.is(':checked') : true
                });
            });
            return out;
        }

        /* ---------- add / edit ---------- */
        function openModal(q) {
            $('#qModalTitle').text(q ? 'Edit question' : 'Add question');
            $('#qId').val(q ? q.id : '');
            $('#qCompetency').val(q ? q.bowdetailsid : '').trigger('change');
            $('#qOptionType').val(q ? q.option_type : 'MC');
            $('#qTaxonomy').val(q ? q.question_type : 'RM');
            $('#qDescription').val(q ? q.description : '');
            renderOptions($('#qOptionType').val(), q ? q.options : null);
            $('#questionModal').modal('show');
        }

        $('#btnAdd').on('click', () => openModal(null));

        $('#tblQuestions').on('click', '.btn-edit', function () {
            $.getJSON(URL.get + $(this).data('id'))
                .done(r => openModal(r.data))
                .fail(x => toastr.error((x.responseJSON || {}).message || 'Could not load the question.'));
        });

        $('#btnSave').on('click', function () {
            const $b = $(this).prop('disabled', true);
            $.ajax({
                url: URL.save, type: 'POST', contentType: 'application/json',
                data: JSON.stringify({
                    id: $('#qId').val(),
                    bowdetailsid: $('#qCompetency').val(),
                    option_type: $('#qOptionType').val(),
                    question_type: $('#qTaxonomy').val(),
                    description: $('#qDescription').val(),
                    options: collectOptions()
                })
            }).done(r => {
                toastr.success(r.message);
                $('#questionModal').modal('hide');
                table.ajax.reload(null, false);
            }).fail(x => toastr.error((x.responseJSON || {}).message || 'Could not save the question.'))
                .always(() => $b.prop('disabled', false));
        });

        /* ---------- delete ---------- */
        $('#tblQuestions').on('click', '.btn-del', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this question?', text: 'Its options are deleted too.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete' })
                .then(res => {
                    if (!res.isConfirmed) return;
                    $.post(URL.remove + id)
                        .done(r => { toastr.success(r.message); table.ajax.reload(null, false); })
                        .fail(x => toastr.error((x.responseJSON || {}).message || 'Could not delete the question.'));
                });
        });
    });
</script>
<?= $this->endSection() ?>