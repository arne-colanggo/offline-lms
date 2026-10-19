<?= $this->extend('backend/layout/pages-layout') ?>

<?= $this->section('stylesheets') ?>
<style>
    .tile {
        text-align: center;
        padding: 12px;
    }

    .tile .n {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.1;
    }

    .dot {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        margin-right: 6px;
        background: #adb5bd;
    }

    .dot.live {
        background: #28a745;
    }

    .dot.off {
        background: #dc3545;
    }

    #feed {
        max-height: 520px;
        overflow-y: auto;
        font-size: 13px;
    }

    #feed .f {
        padding: 6px 0;
        border-bottom: 1px solid #eceef1;
    }

    #feed .f.bad {
        color: #c0392b;
    }

    .ans-cell {
        white-space: normal;
        max-width: 260px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="title">
                <h4>Monitor: <?= esc($scope['class']['subject_name']) ?></h4>
                <small class="text-muted">
                    <?= esc($quizTypes[$quiz['quiz_type']] ?? $quiz['quiz_type']) ?> &middot; Term
                    <?= (int) $quiz['term'] ?> &middot; <?= esc($quiz['quizcode']) ?>
                    &middot; <?= $quiz['isactive'] === 'T' ? 'Open to students' : 'Closed to new starts' ?>
                    <?= $quiz['time_limit'] ? '&middot; ' . (int) $quiz['time_limit'] . ' min' : '' ?>
                </small>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 text-md-right">
            <span class="text-muted mr-2" id="liveStamp"></span>
            <a href="<?= esc($scope['urls']['quizzes']) ?>" class="btn btn-outline-secondary">Back to quizzes</a>
        </div>
    </div>
</div>

<div class="row mb-10" id="tiles">
    <?php foreach (['enrolled' => 'Enrolled', 'not_started' => 'Not started', 'in_progress' => 'Taking now', 'offline' => 'Disconnected', 'submitted' => 'Submitted', 'flagged' => 'Auto-submitted (warnings)'] as $k => $label): ?>
        <div class="col-6 col-md-2 mb-10">
            <div class="card-box tile">
                <div class="n" id="t_<?= $k ?>">0</div>
                <div class="text-secondary font-12"><?= $label ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-xl-9 mb-30">
        <div class="card">
            <div class="card-body pd-15">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tblLive">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Status</th>
                                <th style="min-width:150px">Progress</th>
                                <th>Correct</th>
                                <th>Wrong</th>
                                <th>Score</th>
                                <th>Now on</th>
                                <th>Warnings</th>
                                <th>Last seen</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <div class="col-xl-3 mb-30">
        <div class="card">
            <div class="card-body pd-15">
                <h6 class="mb-2">Live activity</h6>
                <div id="feed"></div>
            </div>


        </div>
    </div>
</div>

<!-- Student detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dTitle">Student</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="dMeta" class="mb-3 text-secondary"></div>
                <h6>Answers</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm" id="dAnswers">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Student's answer</th>
                                <th>Correct answer</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <h6>Activity log (newest first)</h6>
                <div id="dEvents" style="max-height:240px; overflow-y:auto; font-size:13px"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>


<script>
    $(function () {
        // CSRF: send the current token on every request and pick up the regenerated one from each JSON response
        const CSRF = { value: '<?= csrf_hash() ?>' };
        $.ajaxSetup({ beforeSend: xhr => xhr.setRequestHeader('X-CSRF-TOKEN', CSRF.value) });
        $(document).ajaxComplete((e, xhr) => { if (xhr.responseJSON && xhr.responseJSON.token) CSRF.value = xhr.responseJSON.token; });

        const U = <?= json_encode($urls) ?>;
        const at = (tpl, id) => tpl.replace('{id}', id);
        const esc = s => $('<div>').text(s == null ? '' : s).html();
        const fmtScore = n => (Math.round(n * 100) / 100).toString();
        const ago = s => s == null ? '—' : s < 5 ? 'now' : s < 60 ? s + 's ago' : s < 3600 ? Math.floor(s / 60) + 'm ago' : Math.floor(s / 3600) + 'h ago';

        const EVENT_TEXT = {
            started: 'started the quiz', resumed: 'resumed the quiz', submitted: 'submitted the quiz', auto_submitted: 'was auto-submitted',
            question_viewed: 'moved to', answered: 'answered', answer_changed: 'changed the answer to',
            tab_hidden: 'switched to another tab', window_blur: 'left the quiz window', fullscreen_exit: 'left full screen',
            copy: 'tried to copy', cut: 'tried to cut', paste: 'tried to paste', contextmenu: 'opened the right-click menu',
            shortcut_blocked: 'pressed a blocked shortcut', fullscreen_denied: 'could not enter full screen'
        };
        const BAD = ['tab_hidden', 'window_blur', 'fullscreen_exit', 'copy', 'cut', 'paste', 'shortcut_blocked', 'contextmenu', 'fullscreen_denied', 'auto_submitted'];
        const evText = e => (EVENT_TEXT[e.event_type] || e.event_type) + (e.detail ? ' ' + e.detail : '');

        function badge(r) {
            switch (r.state) {
                case 'not_started': return '<span class="dot"></span>Not started';
                case 'in_progress': return '<span class="dot live"></span>Taking';
                case 'offline': return '<span class="dot off"></span>Disconnected';
                case 'submitted': return '<span class="badge badge-success">Submitted</span>';
                case 'auto_submitted': return '<span class="badge badge-warning" title="' + esc(r.reason) + '">Auto-submitted</span>'
                    + '<br><small class="text-muted">' + esc({ time_up: 'time ran out', too_many_violations: 'too many warnings', forced_by_teacher: 'by you' }[r.reason] || '') + '</small>';
            }
            return '';
        }

        let total = 0;
        function render(d) {
            total = d.total;
            Object.keys(d.summary).forEach(k => $('#t_' + k).text(d.summary[k]));

            const $tb = $('#tblLive tbody').empty();
            d.rows.forEach(r => {
                if (!r.attemptid) {
                    $tb.append('<tr class="text-muted"><td>' + esc(r.name) + '</td><td>' + badge(r) + '</td><td colspan="8"></td></tr>');
                    return;
                }
                const pct = total ? Math.round(r.answered / total * 100) : 0;
                const live = r.state === 'in_progress' || r.state === 'offline';
                const warn = r.violations > 0 ? '<span class="badge badge-' + (r.violations >= 3 ? 'danger' : 'warning') + '">' + r.violations + '</span>' : '0';
                $tb.append(
                    '<tr>' +
                    '<td><strong>' + esc(r.name) + '</strong></td>' +
                    '<td>' + badge(r) + '</td>' +
                    '<td><div class="progress" style="height:8px"><div class="progress-bar bg-info" style="width:' + pct + '%"></div></div>' +
                    '<small>' + r.answered + ' / ' + total + ' answered</small></td>' +
                    '<td class="text-success weight-600">' + r.correct + '</td>' +
                    '<td class="text-danger weight-600">' + r.wrong + (r.pending ? ' <small class="text-muted">(+' + r.pending + ' essay)</small>' : '') + '</td>' +
                    '<td>' + fmtScore(r.score) + ' / ' + total + '</td>' +
                    '<td>' + (live ? 'Q' + r.current : '—') + '</td>' +
                    '<td>' + warn + '</td>' +
                    '<td>' + (live ? ago(r.seen_ago) : '—') + '</td>' +
                    '<td class="text-nowrap text-right">' +
                    '<button class="btn btn-sm btn-outline-primary btn-detail" data-id="' + r.attemptid + '" data-name="' + esc(r.name) + '">Details</button> ' +
                    (live ? '<button class="btn btn-sm btn-outline-warning btn-force" data-id="' + r.attemptid + '" data-name="' + esc(r.name) + '">Submit</button> ' : '') +
                    '<button class="btn btn-sm btn-outline-danger btn-reset" data-id="' + r.attemptid + '" data-name="' + esc(r.name) + '">Reset</button>' +
                    '</td></tr>');
            });

            const $f = $('#feed').empty();
            if (!d.feed.length) $f.append('<div class="text-muted">No activity yet.</div>');
            d.feed.forEach(e => $f.append('<div class="f ' + (BAD.includes(e.event_type) ? 'bad' : '') + '"><strong>' + esc(e.name) + '</strong> ' + esc(evText(e)) + '<br><small class="text-muted">' + ago(e.ago) + '</small></div>'));
        }

        /* ---------- live polling ---------- */
        let pollTimer = null;
        function poll() {
            clearTimeout(pollTimer);
            $.getJSON(U.data).done(d => { render(d); $('#liveStamp').html('<span class="dot live"></span>Live'); })
                .fail(() => $('#liveStamp').html('<span class="dot off"></span>Reconnecting...'))
                .always(() => { pollTimer = setTimeout(poll, 4000); });
        }
        poll();

        /* ---------- student detail ---------- */
        let openAttempt = null;
        function loadDetail(id, name) {
            $.getJSON(at(U.attempt, id)).done(r => {
                openAttempt = id;
                const a = r.attempt;
                $('#dTitle').text(name);
                $('#dMeta').html('Score <strong>' + fmtScore(a.score) + ' / ' + total + '</strong> &middot; Warnings <strong>' + a.violations + '</strong> &middot; Started ' + esc(a.started_at)
                    + (a.submitted ? ' &middot; Ended ' + esc(a.submitted) : '') + '<br><small>IP ' + esc(a.ip) + ' &middot; ' + esc(a.agent) + '</small>');

                const $b = $('#dAnswers tbody').empty();
                r.rows.forEach(x => {
                    let res;
                    if (!x.answered) res = '<span class="text-muted">Not answered</span>';
                    else if (x.is_correct === null) res = 'Needs grading<br>' + [1, 0.5, 0].map(v => '<button class="btn btn-sm py-0 btn-outline-primary btn-grade mr-1" data-answer="' + x.answerid + '" data-score="' + v + '">' + v + '</button>').join('');
                    else if (x.type === 'ES') res = fmtScore(x.score) + ' <small class="text-muted">(graded)</small> ' + [1, 0.5, 0].map(v => '<button class="btn btn-sm py-0 btn-outline-secondary btn-grade ml-1" data-answer="' + x.answerid + '" data-score="' + v + '">' + v + '</button>').join('');
                    else res = x.is_correct ? '<span class="text-success weight-600">Correct</span>' : (Number(x.score) > 0 ? '<span class="text-warning weight-600">Partial ' + fmtScore(Number(x.score)) + '</span>' : '<span class="text-danger weight-600">Wrong</span>');
                    $b.append('<tr><td>' + x.no + '</td><td class="ans-cell">' + esc(x.question) + '</td><td>' + esc(x.type_label) + '</td><td class="ans-cell">' + esc(x.answer) + '</td><td class="ans-cell">' + esc(x.correct) + '</td><td>' + res + '</td></tr>');
                });

                const $e = $('#dEvents').empty();
                r.events.forEach(e => $e.append('<div class="' + (BAD.includes(e.event_type) ? 'text-danger' : '') + '">' + esc(e.created_at) + ' &mdash; ' + esc(evText(e)) + '</div>'));
                $('#detailModal').modal('show');
            }).fail(x => toastr.error((x.responseJSON || {}).message || 'Could not load the details.'));
        }

        $('#tblLive').on('click', '.btn-detail', function () { loadDetail($(this).data('id'), $(this).data('name')); });

        $('#dAnswers').on('click', '.btn-grade', function () {
            $.ajax({ url: at(U.grade, openAttempt), type: 'POST', contentType: 'application/json', data: JSON.stringify({ answerid: $(this).data('answer'), score: $(this).data('score') }) })
                .done(r => { toastr.success(r.message); loadDetail(openAttempt, $('#dTitle').text()); poll(); })
                .fail(x => toastr.error((x.responseJSON || {}).message || 'Could not grade.'));
        });

        /* ---------- teacher actions ---------- */
        function act(url, title, text, btn) {
            swal({ title: title, text: text, type: 'warning', showCancelButton: true, confirmButtonText: btn }).then(res => {
                if (res.isConfirmed) return;
                $.post(url).done(r => { toastr.success(r.message); poll(); })
                    .fail(x => toastr.error((x.responseJSON || {}).message || 'Action failed.'));
            });
        }
        $('#tblLive').on('click', '.btn-force', function () {
            act(at(U.force, $(this).data('id')), 'Submit for ' + $(this).data('name') + '?', 'Their answers so far are kept and the quiz ends for them.', 'Submit now');
        });
        $('#tblLive').on('click', '.btn-reset', function () {
            act(at(U.reset, $(this).data('id')), 'Reset ' + $(this).data('name') + '?', 'Their answers and activity log are deleted and they can start the quiz again.', 'Reset attempt');
        });
    });
</script>
<?= $this->endSection() ?>