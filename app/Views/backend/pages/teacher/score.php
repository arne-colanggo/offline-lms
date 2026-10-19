<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="card-box mb-30 pd-20">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h4 class="text-blue h4 mb-0"><?= esc($task['title']) ?></h4>
            <small class="text-muted">Rubric: <?= esc($rubric['name']) ?> &middot; Max score: <?= rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.') ?></small>
        </div>
        <a href="<?= route_to('get.performancetasks') ?>" class="btn btn-light btn-sm">Back</a>
    </div>

    <?php if ($task['instructions']): ?>
        <div class="alert alert-light border"><?= nl2br(esc($task['instructions'])) ?></div>
    <?php endif ?>

    <div class="form-group" style="max-width:420px">
        <label>Student</label>
        <select id="student" class="form-control">
            <option value="">Select student</option>
            <?php foreach ($students as $s): $sc = $scored[$s['lrn']] ?? null; ?>
                <option value="<?= esc($s['lrn']) ?>"><?= esc($s['name']) ?><?= $sc ? ' — ' . (float) $sc['total_score'] . '/' . (float) $sc['max_score'] : '' ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div class="table-responsive" id="rubricWrap" style="display:none">
        <table class="table table-bordered">
            <thead><tr><th>Criterion</th>
                <?php foreach ($rubric['levels'] as $l): ?>
                    <th class="text-center"><?= esc($l['label']) ?><br><small>(<?= (float) $l['points'] ?> pts)</small></th>
                <?php endforeach ?>
            </tr></thead>
            <tbody>
            <?php foreach ($rubric['criteria'] as $c): ?>
                <tr>
                    <td><strong><?= esc($c['name']) ?></strong><br><small class="text-muted">x<?= (float) $c['weight'] ?></small></td>
                    <?php foreach ($rubric['levels'] as $i => $l): ?>
                        <td>
                            <label class="d-block mb-0" style="cursor:pointer">
                                <input type="radio" name="c<?= $c['id'] ?>" value="<?= $l['id'] ?>"
                                       data-pts="<?= (float) $l['points'] * (float) $c['weight'] ?>" class="mr-1">
                                <small><?= esc($c['descriptors'][$i]) ?></small>
                            </label>
                        </td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <div class="form-group"><label>Feedback</label><textarea id="feedback" class="form-control" rows="3"></textarea></div>
        <div class="d-flex align-items-center">
            <h5 class="mb-0 mr-3">Total: <span id="total">0</span> / <?= (float) $max ?></h5>
            <button class="btn btn-primary" id="save">Save Score</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const TASK = <?= (int) $task['id'] ?>;
const CRITERIA = <?= json_encode(array_column($rubric['criteria'], 'id')) ?>;

function recalc() {
    let t = 0;
    $('#rubricWrap input[type=radio]:checked').each(function () { t += parseFloat($(this).data('pts')) || 0; });
    $('#total').text(+t.toFixed(2));
}
$('#rubricWrap').on('change', 'input[type=radio]', recalc);

$('#student').on('change', function () {
    const lrn = $(this).val();
    $('#rubricWrap input[type=radio]').prop('checked', false); $('#feedback').val('');
    if (!lrn) { $('#rubricWrap').hide(); return; }
    $.getJSON('<?= base_url('admin/performance-task') ?>/' + TASK + '/student/' + encodeURIComponent(lrn), function (r) {
        Object.entries(r.levels).forEach(([cid, lid]) => $(`input[name=c${cid}][value=${lid}]`).prop('checked', true));
        $('#feedback').val(r.feedback); recalc(); $('#rubricWrap').show();
    });
});

$('#save').on('click', function () {
    const levels = {};
    for (const id of CRITERIA) {
        const v = $(`input[name=c${id}]:checked`).val();
        if (!v) { toastr.error('Rate every criterion before saving.'); return; }
        levels[id] = v;
    }
    const btn = $(this).prop('disabled', true);
    $.ajax({
        url: '<?= base_url('admin/performance-task') ?>/' + TASK + '/score', method: 'POST', contentType: 'application/json', dataType: 'json',
        data: JSON.stringify({ lrn: $('#student').val(), levels, feedback: $('#feedback').val() }),
        success: r => { btn.prop('disabled', false); r.status ? toastr.success(r.message + ' (' + r.total + '/' + r.max + ')') : toastr.error(r.message); },
        error: () => { btn.prop('disabled', false); toastr.error('Request failed.'); }
    });
});
</script>
<?= $this->endSection() ?>
