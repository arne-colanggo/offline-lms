<?php
$scope = $scope ?? null;   // class context when opened from a Class page
$urls = $scope['urls'] ?? [
    'list' => route_to('get.questions.list'),
    'sync' => route_to('post.quiz.sync', $quiz['id']),
    'quizzes' => route_to('get.quizzes'),
    'questionsPage' => route_to('get.questions'),
];
?>
<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/vendors/plugins/datatables/css/dataTables.bootstrap4.min.css">
<style>
    .q-text {
        max-width: 380px;
        white-space: normal;
    }

    #selectedList {
        max-height: 360px;
        overflow-y: auto;
    }

    #selectedList .sel-item {
        display: flex;
        gap: .5rem;
        align-items: flex-start;
        padding: .5rem 0;
        border-bottom: 1px solid #eceef1;
    }

    #selectedList .sel-item:last-child {
        border-bottom: 0;
    }

    #selectedList .sel-text {
        flex: 1;
        font-size: 14px;
    }

    .sticky-side {
        position: sticky;
        top: 90px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4>Quiz
                    <?= esc($quiz['quizcode']) ?>
                </h4>
                <small class="text-muted">
                    <?= esc($quiz['section']) ?> &middot;
                    <?= esc($quiz['schoolyear']) ?>
                </small>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 text-md-right">
            <a href="<?= esc($urls['quizzes']) ?>" class="btn btn-outline-secondary mr-2">Back to quizzes</a>
            <a href="<?= esc($urls['questionsPage']) ?>" class="btn btn-outline-primary">Add new questions</a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Question bank -->
    <div class="col-lg-8">
        <div class="card-box mb-30">
            <div class="pd-20">
                <h5 class="mb-3">Choose questions</h5>
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
                <table class="table table-striped w-100" id="tblPick">
                    <thead>
                        <tr>
                            <th style="width:36px"></th>
                            <th>Question</th>
                            <th>Competency</th>
                            <th>Type</th>
                            <th>Level</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Quiz settings + selected -->
    <div class="col-lg-4">
        <div class="sticky-side">
            <div class="card-box mb-30 pd-20">
                <h5 class="mb-3">Quiz settings</h5>
                <div class="form-row">
                    <div class="form-group col-7">
                        <label>Quiz type</label>
                        <select id="sType" class="form-control">
                            <?php foreach ($quizTypes as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $quiz['quiz_type'] === $k ? 'selected' : '' ?>>
                                    <?= esc($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-5">
                        <label>Term</label>
                        <select id="sTerm" class="form-control">
                            <?php for ($t = 1; $t <= 4; $t++): ?>
                                <option value="<?= $t ?>" <?= (int) $quiz['term'] === $t ? 'selected' : '' ?>>Term
                                    <?= $t ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Time limit (minutes)</label>
                        <input type="number" min="1" id="sTime" class="form-control" placeholder="No limit"
                            value="<?= esc($quiz['time_limit'] ?? '') ?>">
                    </div>
                    <div class="form-group col-6">
                        <label>Warnings allowed</label>
                        <input type="number" min="0" max="20" id="sMax" class="form-control"
                            value="<?= (int) ($quiz['max_violations'] ?? 3) ?>">
                    </div>
                </div>
                <small class="form-text text-muted mb-3" style="margin-top:-8px">At the warning limit the quiz is
                    submitted automatically. 0 = only record, never auto-submit.</small>

                <?php
                $switches = [
                    'shuffle_questions' => 'Shuffle questions',
                    'shuffle_options' => 'Shuffle options',
                    'show_answer' => 'Show answers after submitting',
                    'isactive' => 'Open to students',
                ];
                foreach ($switches as $k => $label): ?>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input setting" id="s_<?= $k ?>" data-key="<?= $k ?>"
                            <?= $quiz[$k] === 'T' ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="s_<?= $k ?>">
                            <?= $label ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-box mb-30 pd-20">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Selected questions</h5>
                    <span class="badge badge-primary" id="selCount">0</span>
                </div>
                <div id="selectedList"></div>
                <button class="btn btn-primary btn-block mt-3" id="btnSaveQuiz">Save quiz</button>
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

        const esc = s => $('<div>').text(s == null ? '' : s).html();
        const TYPE_LABEL = <?= json_encode($optionTypes) ?>;

        // id -> {description, option_type}; a Map keeps the order the teacher picked them in
        const selected = new Map();
        <?php foreach ($selected as $s): ?>
            selected.set(<?= (int) $s['id'] ?>, { description: <?= json_encode($s['description'], JSON_HEX_TAG | JSON_HEX_AMP) ?>, option_type: <?= json_encode($s['option_type']) ?> });
        <?php endforeach; ?>

        function renderSelected() {
            const $l = $('#selectedList').empty();
            $('#selCount').text(selected.size);
            if (!selected.size) {
                $l.append('<p class="text-muted mb-0">No questions yet. Tick questions in the table to add them.</p>');
                return;
            }
            selected.forEach((q, id) => {
                const txt = q.description.length > 90 ? q.description.slice(0, 90) + '...' : q.description;
                $l.append(
                    '<div class="sel-item" data-id="' + id + '">' +
                    '<div class="sel-text">' + esc(txt) + '<br><small class="text-muted">' + esc(TYPE_LABEL[q.option_type] || q.option_type) + '</small></div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-unpick" title="Remove">&times;</button></div>');
            });
        }

        const table = $('#tblPick').DataTable({
            ajax: {
                url: '<?= esc($urls['list'], 'js') ?>',
                data: d => {
                    d.bowdetailsid = $('#fCompetency').val();
                    d.option_type = $('#fType').val();
                    d.question_type = $('#fTax').val();
                },
                dataSrc: 'data'
            },
            order: [],
            columns: [
                {
                    data: 'id', orderable: false, render: id =>
                        '<input type="checkbox" class="pick" value="' + id + '"' + (selected.has(+id) ? ' checked' : '') + '>'
                },
                { data: 'description', render: d => '<div class="q-text">' + esc(d.length > 140 ? d.slice(0, 140) + '...' : d) + '</div>' },
                { data: null, render: r => '<strong>' + esc(r.competencycode) + '</strong><br><small class="text-muted">' + esc(r.subject) + '</small>' },
                { data: 'option_label' },
                { data: 'taxonomy_label' }
            ]
        });
        $('#fCompetency, #fType, #fTax').on('change', () => table.ajax.reload());

        $('#tblPick').on('change', '.pick', function () {
            const id = +this.value, row = table.row($(this).closest('tr')).data();
            if (this.checked) selected.set(id, { description: row.description, option_type: row.option_type });
            else selected.delete(id);
            renderSelected();
        });

        $('#selectedList').on('click', '.btn-unpick', function () {
            const id = +$(this).closest('.sel-item').data('id');
            selected.delete(id);
            $('#tblPick .pick[value="' + id + '"]').prop('checked', false);
            renderSelected();
        });

        $('#btnSaveQuiz').on('click', function () {
            const $b = $(this).prop('disabled', true);
            const payload = { quiz_type: $('#sType').val(), term: $('#sTerm').val(), time_limit: $('#sTime').val(), max_violations: $('#sMax').val(), question_ids: Array.from(selected.keys()) };
            $('.setting').each(function () { payload[$(this).data('key')] = this.checked; });

            $.ajax({ url: '<?= esc($urls['sync'], 'js') ?>', type: 'POST', contentType: 'application/json', data: JSON.stringify(payload) })
                .done(r => toastr.success(r.message))
                .fail(x => toastr.error((x.responseJSON || {}).message || 'Could not save the quiz.'))
                .always(() => $b.prop('disabled', false));
        });

        renderSelected();
    });
</script>
<?= $this->endSection() ?>