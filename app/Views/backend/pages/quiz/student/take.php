<?= $this->extend('backend/layout/pages-student-layout') ?>

<?= $this->section('stylesheets') ?>
<style>
    /* distraction-free: hide the template chrome while taking the quiz */
    .left-side-bar,
    .header,
    .mobile-menu-overlay,
    .footer-wrap {
        display: none !important;
    }

    .main-container {
        margin: 0 !important;
        padding: 0 !important;
    }

    .main-container .pd-ltr-20,
    .main-container .xs-pd-20-10 {
        padding: 12px !important;
    }

    .no-select {
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    .q-overlay {
        position: fixed;
        inset: 0;
        background: #fff;
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .q-overlay .box {
        max-width: 560px;
        width: 100%;
    }

    #qBody .opt {
        display: block;
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        padding: 10px 14px;
        margin-bottom: 8px;
        cursor: pointer;
    }

    #qBody .opt:hover {
        background: #f6f8fb;
    }

    #qBody .opt input {
        margin-right: 10px;
    }

    #navDots .btn {
        width: 38px;
        margin: 0 4px 6px 0;
        padding: 4px 0;
    }

    @media print {
        body * {
            display: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="quizApp" class="no-select">

    <!-- Before the quiz starts: the click gives us permission to enter full screen -->
    <div id="startOverlay" class="q-overlay">
        <div class="box text-center">
            <h4><?= esc($subject) ?></h4>
            <p class="text-muted"><?= (int) $total ?>
                questions<?= $quiz['time_limit'] ? ' &middot; ' . (int) $quiz['time_limit'] . ' minutes' : '' ?></p>
            <p>The quiz runs in full screen. Leaving the quiz window is recorded and shown to your teacher.</p>
            <button class="btn btn-primary btn-lg" id="btnBegin">Enter full screen and begin</button>
        </div>
    </div>

    <div id="pausedOverlay" class="q-overlay d-none">
        <div class="box text-center">
            <h4>You left full screen</h4>
            <p>This was recorded. Return to full screen to continue.</p>
            <button class="btn btn-primary btn-lg" id="btnReturn">Return to full screen</button>
        </div>
    </div>

    <div id="lockOverlay" class="q-overlay d-none">
        <div class="box text-center">
            <h4 id="lockTitle">Quiz locked</h4>
            <p id="lockMsg"></p>
            <a class="btn btn-primary" id="lockLink" href="<?= esc($urls['intro']) ?>">Continue</a>
        </div>
    </div>

    <div id="quizMain" class="d-none">
        <div class="card-box pd-15 mb-15 d-flex flex-wrap justify-content-between align-items-center">
            <div><strong><?= esc($subject) ?></strong> &middot; Question <span id="qNo">1</span> of <?= (int) $total ?>
            </div>
            <div class="font-18 weight-600" id="timer"></div>
            <div><span class="badge badge-secondary" id="warnBadge">Warnings 0</span></div>
        </div>

        <div class="card-box pd-20 mb-15">
            <div id="qText" class="font-18 mb-20" style="white-space: pre-wrap"></div>
            <div id="qBody"></div>
        </div>

        <div class="d-flex justify-content-between mb-15">
            <button class="btn btn-outline-secondary" id="btnPrev">Previous</button>
            <div>
                <button class="btn btn-outline-primary" id="btnNext">Next</button>
                <button class="btn btn-success" id="btnSubmit">Submit quiz</button>
            </div>
        </div>
        <div id="navDots" class="card-box pd-15"></div>
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
        const TOTAL = <?= (int) $total ?>;
        const ST = { token: null, index: 0, remaining: null, violations: 0, max: 0, finished: false, locked: false, fs: true, answered: new Set() };
        let current = null;        // question currently on screen
        let dirty = false;
        let saveTimer = null;
        let timers = [];

        const running = () => ST.token && !ST.finished && !ST.locked;

        /* ---------- all POSTs go through one queue: the CSRF token regenerates after each request ---------- */
        let chain = Promise.resolve();
        function post(url, body) {
            const run = () => new Promise(resolve => {
                $.ajax({
                    url: url, type: 'POST', contentType: 'application/json',
                    data: JSON.stringify(Object.assign({ session_token: ST.token }, body || {}))
                })
                    .done(r => resolve(r))
                    .fail(x => resolve(x.responseJSON || { status: false, message: 'Network problem. Check your connection.' }));
            });
            chain = chain.then(run);
            return chain.then(r => { handleCodes(r); return r; });
        }

        function handleCodes(r) {
            if (!r) return;
            if (r.code === 'replaced') lock('Quiz opened elsewhere', r.message, false);
            else if (r.code === 'closed') finish(r.message || 'This quiz has ended.');
        }

        function lock(title, msg, link) {
            ST.locked = true;
            $('#lockTitle').text(title); $('#lockMsg').text(msg);
            $('#lockLink').toggle(!!link);
            $('#lockOverlay').removeClass('d-none');
        }

        function finish(msg) {
            if (ST.finished) return;
            ST.finished = true;
            timers.forEach(clearInterval);
            if (document.fullscreenElement && document.exitFullscreen) document.exitFullscreen().catch(() => { });
            $('#pausedOverlay, #startOverlay').addClass('d-none');
            lock('Quiz finished', msg, true);
        }

        /* ---------- start ---------- */
        $('#btnBegin').on('click', async function () {
            $(this).prop('disabled', true);
            try { await document.documentElement.requestFullscreen(); } catch (e) { ST.fs = false; }

            const r = await post(U.start, {});
            if (!r.status) {
                $(this).prop('disabled', false);
                if (r.code !== 'closed') toastr.error(r.message || 'Could not start the quiz.');
                return;
            }
            ST.token = r.session_token; ST.max = r.max; ST.violations = r.violations; ST.remaining = r.remaining;
            r.answered.forEach(i => ST.answered.add(i));
            $('#startOverlay').addClass('d-none');
            $('#quizMain').removeClass('d-none');
            buildNav(); updateWarn();
            if (!ST.fs) sendEvent('fullscreen_denied');
            startTimers();
            loadQuestion(r.index);
        });

        /* ---------- questions ---------- */
        function buildNav() {
            const $n = $('#navDots').empty();
            for (let i = 0; i < TOTAL; i++) {
                $n.append('<button type="button" class="btn btn-sm btn-outline-secondary" data-i="' + i + '">' + (i + 1) + '</button>');
            }
            paintNav();
        }
        function paintNav() {
            $('#navDots .btn').each(function () {
                const i = +$(this).data('i');
                $(this).toggleClass('btn-success', ST.answered.has(i) && i !== ST.index)
                    .toggleClass('btn-outline-secondary', !ST.answered.has(i) && i !== ST.index)
                    .toggleClass('btn-primary', i === ST.index);
            });
        }
        $('#navDots').on('click', '.btn', function () { goTo(+$(this).data('i')); });
        $('#btnPrev').on('click', () => goTo(ST.index - 1));
        $('#btnNext').on('click', () => goTo(ST.index + 1));

        async function goTo(i) {
            if (i < 0 || i >= TOTAL || !running()) return;
            await saveNow();
            loadQuestion(i);
        }

        function loadQuestion(i) {
            $.getJSON(U.question + i + '?st=' + encodeURIComponent(ST.token))
                .done(r => { ST.index = i; current = r.question; render(r.question); paintNav(); })
                .fail(x => { if (x.responseJSON && x.responseJSON.code) handleCodes(x.responseJSON); else toastr.error('Could not load the question.'); });
        }

        function render(q) {
            $('#qNo').text(q.index + 1);
            $('#qText').text(q.text);
            $('#btnPrev').prop('disabled', q.index === 0);
            $('#btnNext').toggle(q.index < TOTAL - 1);
            const $b = $('#qBody').empty();
            const saved = q.saved;
            dirty = false;

            if (q.type === 'MC' || q.type === 'TF') {
                q.options.forEach(o => {
                    const $l = $('<label class="opt"><input type="radio" name="opt"> <span></span></label>');
                    $l.find('input').val(o.id).prop('checked', saved === o.id);
                    $l.find('span').text(o.text);
                    $b.append($l);
                });
            } else if (q.type === 'ID') {
                $b.append($('<input type="text" class="form-control ans" maxlength="255" placeholder="Your answer" autocomplete="off">').val(saved || ''));
            } else if (q.type === 'FB') {
                for (let k = 0; k < q.blanks; k++) {
                    $b.append($('<div class="mb-2"><label class="mb-1">Blank ' + (k + 1) + '</label><input type="text" class="form-control ans fb" maxlength="255" autocomplete="off"></div>')
                        .find('input').val((saved && saved[k]) || '').end());
                }
            } else if (q.type === 'MT') {
                q.items.forEach(it => {
                    const $r = $('<div class="form-row align-items-center mb-2"><div class="col-md-6 item"></div><div class="col-md-6"><select class="form-control ans mt"><option value="">Select the match</option></select></div></div>');
                    $r.find('.item').text(it.text);
                    const $s = $r.find('select').attr('data-id', it.id);
                    q.choices.forEach(c => $s.append($('<option>').val(c).text(c)));
                    if (saved && saved[it.id]) $s.val(saved[it.id]);
                    $b.append($r);
                });
            } else if (q.type === 'ES') {
                $b.append($('<textarea class="form-control ans" rows="8" maxlength="8000" placeholder="Type your answer"></textarea>').val(saved || ''));
            }
        }

        /* ---------- answers: autosave ---------- */
        function collect() {
            if (!current) return null;
            switch (current.type) {
                case 'MC': case 'TF': { const v = $('#qBody input[name=opt]:checked').val(); return v ? +v : null; }
                case 'ID': case 'ES': return $('#qBody .ans').val();
                case 'FB': return $('#qBody .fb').map(function () { return $(this).val(); }).get();
                case 'MT': { const m = {}; $('#qBody select.mt').each(function () { m[$(this).data('id')] = $(this).val(); }); return m; }
            }
            return null;
        }
        function hasAnswer(a) {
            if (a === null || a === undefined) return false;
            if (Array.isArray(a)) return a.some(x => String(x).trim() !== '');
            if (typeof a === 'object') return Object.values(a).some(x => String(x).trim() !== '');
            return String(a).trim() !== '';
        }

        async function saveNow() {
            clearTimeout(saveTimer);
            if (!dirty || !current) return;
            dirty = false;
            const a = collect();
            if (!hasAnswer(a)) return;
            const r = await post(U.answer, { index: current.index, answer: a });
            if (r.status) { ST.answered.add(current.index); paintNav(); }
            else if (!r.code) toastr.error(r.message || 'Could not save your answer.');
        }

        $('#qBody').on('change', 'input[name=opt], select.mt', () => { dirty = true; saveNow(); });
        $('#qBody').on('input', 'input.ans, textarea.ans', () => { dirty = true; clearTimeout(saveTimer); saveTimer = setTimeout(saveNow, 800); });

        /* ---------- submit ---------- */
        $('#btnSubmit').on('click', async function () {
            await saveNow();
            const left = TOTAL - ST.answered.size;
            const res = await swal({
                title: 'Submit your quiz?',
                text: left > 0 ? 'You have ' + left + ' unanswered question' + (left === 1 ? '' : 's') + '. You cannot change your answers after submitting.' : 'You cannot change your answers after submitting.',
                type: 'question', showCancelButton: true, confirmButtonText: 'Submit'
            });
            if (res.isConfirmed) return;
            const r = await post(U.submit, {});
            if (r.status) finish(r.show ? 'Submitted. Your score is ' + r.score + ' out of ' + r.total + '.' : 'Your quiz was submitted. Your teacher will share your results.');
            else if (!r.code) toastr.error(r.message || 'Could not submit.');
        });

        /* ---------- timer + heartbeat ---------- */
        function fmt(s) { const m = Math.floor(s / 60), x = s % 60; return (m < 10 ? '0' : '') + m + ':' + (x < 10 ? '0' : '') + x; }
        function startTimers() {
            timers.push(setInterval(() => {
                if (ST.remaining === null || !running()) return;
                ST.remaining = Math.max(0, ST.remaining - 1);
                $('#timer').text('Time left ' + fmt(ST.remaining)).toggleClass('text-danger', ST.remaining <= 60);
                if (ST.remaining === 0) heartbeat();       // the server submits the quiz and answers 'closed'
            }, 1000));
            timers.push(setInterval(heartbeat, 10000));
        }
        async function heartbeat() {
            if (!running()) return;
            const r = await post(U.heartbeat, { index: ST.index });
            if (r.status && r.remaining !== null) ST.remaining = r.remaining;
        }

        /* ---------- anti-cheat ---------- */
        function updateWarn() {
            $('#warnBadge').text(ST.max > 0 ? 'Warnings ' + ST.violations + ' / ' + ST.max : 'Warnings ' + ST.violations)
                .toggleClass('badge-secondary', ST.violations === 0).toggleClass('badge-danger', ST.violations > 0);
        }

        async function sendEvent(type, detail) {
            if (!running()) return;
            const r = await post(U.event, { event_type: type, detail: detail || null });
            if (!r.status) return;
            ST.violations = r.violations; updateWarn();
            if (r.terminated) { finish('Your quiz was submitted automatically after too many warnings.'); return; }
            if (r.counted && ST.max > 0) {
                swal({
                    type: 'warning', title: 'Warning ' + r.violations + ' of ' + r.max,
                    text: 'Leaving the quiz, copying or pasting is recorded and your teacher can see it. At ' + r.max + ' warnings your quiz is submitted automatically.',
                    confirmButtonText: 'I understand'
                });
            }
        }

        document.addEventListener('visibilitychange', () => { if (document.hidden && running()) sendEvent('tab_hidden'); });
        window.addEventListener('blur', () => {
            if (!running() || document.hidden) return;
            setTimeout(() => { if (running() && !document.hasFocus() && !document.hidden) sendEvent('window_blur'); }, 300);
        });
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement && running() && ST.fs) {
                $('#pausedOverlay').removeClass('d-none');
                sendEvent('fullscreen_exit');
            }
        });
        $('#btnReturn').on('click', async () => {
            try { await document.documentElement.requestFullscreen(); $('#pausedOverlay').addClass('d-none'); } catch (e) { toastr.error('Could not enter full screen.'); }
        });

        ['copy', 'cut', 'paste', 'contextmenu'].forEach(ev => document.addEventListener(ev, e => {
            if (!running()) return;
            e.preventDefault();
            sendEvent(ev);
        }));
        document.addEventListener('dragstart', e => { if (running()) e.preventDefault(); });
        document.addEventListener('selectstart', e => { if (running() && !$(e.target).is('input, textarea')) e.preventDefault(); });

        document.addEventListener('keydown', e => {
            if (!running()) return;
            const k = (e.key || '').toLowerCase(), ctrl = e.ctrlKey || e.metaKey;
            const blocked = k === 'f12' || k === 'printscreen'
                || (ctrl && e.shiftKey && ['i', 'j', 'c'].includes(k))
                || (ctrl && ['u', 's', 'p', 'c', 'v', 'x'].includes(k));
            if (blocked) {
                e.preventDefault();
                sendEvent('shortcut_blocked', (ctrl ? 'Ctrl+' : '') + (e.shiftKey ? 'Shift+' : '') + e.key);
            }
        });

        window.addEventListener('beforeunload', e => { if (running()) { e.preventDefault(); e.returnValue = ''; } });
    });
</script>
<?= $this->endSection() ?>