<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('content') ?>
<div class="card-box mb-30 pd-20">
    <h4 class="text-blue h4 mb-3"><?= esc($pageTitle) ?></h4>

    <?php if ($inUse): ?>
        <div class="alert alert-warning">This rubric is used by a performance task. Only the name and description can be
            changed.</div>
    <?php endif ?>

    <div class="form-group"><label>Rubric name</label>
        <input type="text" id="rName" class="form-control" maxlength="150">
    </div>
    <div class="form-group"><label>Description</label>
        <textarea id="rDesc" class="form-control" rows="2"></textarea>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="grid">
            <thead id="gridHead"></thead>
            <tbody id="gridBody"></tbody>
        </table>
    </div>

    <?php if (!$inUse): ?>
        <button class="btn btn-sm btn-outline-secondary" id="addLevel">+ Level (column)</button>
        <button class="btn btn-sm btn-outline-secondary" id="addCrit">+ Criterion (row)</button>
    <?php endif ?>

    <div class="mt-3">
        <span class="mr-3">Maximum score: <strong id="maxScore">0</strong></span>
        <button class="btn btn-primary" id="save">Save Rubric</button>
        <a href="<?= route_to('get.rubrics') ?>" class="btn btn-light">Cancel</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const LOCKED = <?= $inUse ? 'true' : 'false' ?>;
    const state = <?= json_encode($rubric, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    state.levels = state.levels.map(l => ({ label: l.label, points: l.points }));
    state.criteria = state.criteria.map(c => ({ name: c.name, weight: c.weight, descriptors: c.descriptors.slice() }));
    const dis = LOCKED ? 'disabled' : '';

    function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); }

    function render() {
        let head = '<tr><th style="min-width:200px">Criterion / Weight</th>';
        state.levels.forEach((l, i) => {
            head += `<th style="min-width:170px">
            <input class="form-control form-control-sm mb-1" data-k="lvl-label" data-i="${i}" value="${esc(l.label)}" placeholder="Label" ${dis}>
            <input type="number" step="0.01" class="form-control form-control-sm" data-k="lvl-points" data-i="${i}" value="${esc(l.points)}" placeholder="Points" ${dis}>
            ${LOCKED ? '' : `<a href="#" class="text-danger small rm-level" data-i="${i}">remove</a>`}</th>`;
        });
        $('#gridHead').html(head + '</tr>');

        let body = '';
        state.criteria.forEach((c, j) => {
            body += `<tr><td>
            <input class="form-control form-control-sm mb-1" data-k="c-name" data-j="${j}" value="${esc(c.name)}" placeholder="Criterion" ${dis}>
            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" data-k="c-weight" data-j="${j}" value="${esc(c.weight)}" placeholder="Weight" ${dis}>
            ${LOCKED ? '' : `<a href="#" class="text-danger small rm-crit" data-j="${j}">remove</a>`}</td>`;
            state.levels.forEach((l, i) => {
                body += `<td><textarea class="form-control form-control-sm" rows="3" data-k="c-desc" data-j="${j}" data-i="${i}" ${dis}>${esc(c.descriptors[i])}</textarea></td>`;
            });
            body += '</tr>';
        });
        $('#gridBody').html(body);
        updateMax();
    }

    function updateMax() {
        const top = Math.max(0, ...state.levels.map(l => parseFloat(l.points) || 0));
        const w = state.criteria.reduce((a, c) => a + (parseFloat(c.weight) || 0), 0);
        $('#maxScore').text((top * w).toFixed(2).replace(/\.00$/, ''));
    }

    $('#rName').val(state.name); $('#rDesc').val(state.description);

    $('#grid').on('input', '[data-k]', function () {
        const e = $(this), k = e.data('k'), i = e.data('i'), j = e.data('j'), v = e.val();
        if (k === 'lvl-label') state.levels[i].label = v;
        if (k === 'lvl-points') state.levels[i].points = v;
        if (k === 'c-name') state.criteria[j].name = v;
        if (k === 'c-weight') state.criteria[j].weight = v;
        if (k === 'c-desc') state.criteria[j].descriptors[i] = v;
        updateMax();
    });
    $('#addLevel').on('click', () => { state.levels.push({ label: '', points: 0 }); state.criteria.forEach(c => c.descriptors.push('')); render(); });
    $('#addCrit').on('click', () => { state.criteria.push({ name: '', weight: 1, descriptors: state.levels.map(() => '') }); render(); });
    $('#grid').on('click', '.rm-level', function (e) {
        e.preventDefault(); const i = $(this).data('i');
        state.levels.splice(i, 1); state.criteria.forEach(c => c.descriptors.splice(i, 1)); render();
    });
    $('#grid').on('click', '.rm-crit', function (e) { e.preventDefault(); state.criteria.splice($(this).data('j'), 1); render(); });



    $('#save').on('click', function () {
        const btn = $(this).prop('disabled', true);
        $.ajax({
            url: '<?= route_to('post.rubric') ?>', method: 'POST', contentType: 'application/json', dataType: 'json',
            data: JSON.stringify({ id: state.id, name: $('#rName').val(), description: $('#rDesc').val(), levels: state.levels, criteria: state.criteria, class_id: <?= $classId ?> }),
            success: r => r.status ? (toastr.success(r.message), setTimeout(() => location.href = r.redirect, 600)) : (toastr.error(r.message), btn.prop('disabled', false)),
            error: () => { toastr.error('Request failed.'); btn.prop('disabled', false); }
        });
    });
    render();
</script>
<?= $this->endSection() ?>