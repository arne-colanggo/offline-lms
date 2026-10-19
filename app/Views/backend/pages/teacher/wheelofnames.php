<?= $this->extend('backend/layout/pages-teacher-layout') ?>

<?= $this->section('content') ?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Wheel of Names</title>
<style>
    :root {
        --paper: #f3f4f7;
        --card: #ffffff;
        --ink: #1d2340;
        --muted: #6b7088;
        --line: #dfe2ea;
        --brass: #b08d4a;
        --brass-2: #d9bd82;
        color-scheme: light;
    }

    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
    }

    body {
        background: radial-gradient(1200px 700px at 30% -10%, #ffffff 0%, var(--paper) 60%);
        color: var(--ink);
        font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        min-height: 100vh;
        padding: 28px 20px;
    }

    header {
        max-width: 1060px;
        margin: 0 auto 22px;
    }

    h1 {
        font-family: "Palatino Linotype", Palatino, Georgia, serif;
        font-size: 2.1rem;
        font-weight: 600;
        letter-spacing: .01em;
        margin: 0;
    }

    header p {
        margin: 4px 0 0;
        color: var(--muted);
    }

    .layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 32px;
        max-width: 1060px;
        margin: 0 auto;
        align-items: start;
    }

    .stage {
        display: grid;
        place-items: center;
    }

    .wrap {
        position: relative;
        width: 100%;
        max-width: 580px;
        aspect-ratio: 1;
    }

    .wrap::before {
        content: "";
        position: absolute;
        inset: 4% 6% -2%;
        background: rgba(29, 35, 64, .22);
        filter: blur(26px);
        border-radius: 50%;
    }

    canvas {
        position: relative;
        width: 100%;
        height: 100%;
        display: block;
        cursor: pointer;
        border-radius: 50%;
    }

    .pointer {
        position: absolute;
        top: -10px;
        left: 50%;
        width: 38px;
        height: 52px;
        margin-left: -19px;
        z-index: 2;
        pointer-events: none;
        transform-origin: 50% 20%;
        filter: drop-shadow(0 3px 3px rgba(29, 35, 64, .35));
    }

    aside {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 10px 30px rgba(29, 35, 64, .08);
    }

    label.t {
        display: block;
        font-family: "Palatino Linotype", Palatino, Georgia, serif;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    textarea {
        width: 100%;
        height: 180px;
        resize: vertical;
        background: #fafbfc;
        color: var(--ink);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 12px;
        font: inherit;
        line-height: 1.5;
    }

    .row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
        align-items: center;
    }

    .row label {
        font-size: .93rem;
        color: var(--muted);
        display: flex;
        gap: 8px;
        align-items: center;
    }

    button {
        font: inherit;
        cursor: pointer;
        border-radius: 999px;
        padding: 8px 16px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink);
        transition: border-color .15s, background .15s;
    }

    button:hover:not(:disabled) {
        border-color: var(--brass);
    }

    button.primary {
        width: 100%;
        margin-top: 16px;
        padding: 13px;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: .02em;
        color: #fff;
        border: 0;
        background: linear-gradient(180deg, #2c3563, var(--ink));
        box-shadow: 0 6px 16px rgba(29, 35, 64, .28);
    }

    button.primary:hover:not(:disabled) {
        background: linear-gradient(180deg, #36427a, #232b52);
    }

    button:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    button:focus-visible,
    textarea:focus-visible,
    input:focus-visible {
        outline: 3px solid var(--brass-2);
        outline-offset: 2px;
    }

    .count {
        font-size: .9rem;
        color: var(--muted);
        margin-left: auto;
    }

    .winner {
        margin: 20px 0 6px;
        padding: 16px 12px;
        min-height: 92px;
        text-align: center;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
    }

    .winner .small {
        font-size: .9rem;
        color: var(--muted);
        min-height: 1.2em;
    }

    .winner .name {
        font-family: "Palatino Linotype", Palatino, Georgia, serif;
        font-size: 1.9rem;
        font-weight: 600;
        color: var(--ink);
        overflow-wrap: anywhere;
    }

    .winner .name.show {
        animation: rise .5s ease-out;
    }

    @keyframes rise {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    h2 {
        font-family: "Palatino Linotype", Palatino, Georgia, serif;
        font-size: 1.05rem;
        font-weight: 600;
        margin: 14px 0 0;
    }

    ol#history {
        margin: 6px 0 0;
        padding-left: 22px;
        max-height: 120px;
        overflow: auto;
        color: var(--muted);
        font-size: .95rem;
    }

    @media (max-width: 860px) {
        .layout {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .winner .name.show {
            animation: none;
        }
    }
</style>

<header>
    <h1>Wheel of Names</h1>
    <p>Spin to choose a student at random.</p>
</header>
<div class="layout">
    <div class="stage">
        <div class="wrap">
            <svg class="pointer" id="pointer" viewBox="0 0 38 52" aria-hidden="true">
                <defs>
                    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#e6cf9c" />
                        <stop offset="1" stop-color="#a98440" />
                    </linearGradient>
                </defs>
                <path d="M19 50 C19 50 3 30 3 18 A16 16 0 1 1 35 18 C35 30 19 50 19 50Z" fill="url(#bg)" stroke="#fff"
                    stroke-width="2" />
                <circle cx="19" cy="18" r="6" fill="#1d2340" />
            </svg>
            <canvas id="wheel" role="img" aria-label="Wheel of names. Press Spin to pick a name."></canvas>
        </div>
    </div>
    <aside>
        <label class="t" for="names">Names</label>
        <textarea id="names" spellcheck="false" placeholder="One name per line"></textarea>
        <div class="row">
            <button type="button" id="shuffle">Shuffle</button>
            <button type="button" id="reset">Restore all</button>
            <span class="count" id="count"></span>
        </div>
        <div class="row"><label><input type="checkbox" id="removeAfter"> Remove winner after each spin</label></div>
        <div class="row"><label><input type="checkbox" id="sound" checked> Play music while spinning</label></div>
        <button type="button" class="primary" id="spin">Spin the wheel</button>
        <div class="winner" aria-live="polite">
            <div class="small" id="wlabel"></div>
            <div class="name" id="wname"></div>
        </div>
        <h2>Picked so far</h2>
        <ol id="history"></ol>
    </aside>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    (function () {
        var COLORS = ['#2c3563', '#5f8173', '#b8707f', '#a8803f', '#4f6c9c', '#6b4a73'];
        var TAU = Math.PI * 2;
        var canvas = document.getElementById('wheel'), ctx = canvas.getContext('2d');
        var ta = document.getElementById('names'), spinBtn = document.getElementById('spin');
        var pointer = document.getElementById('pointer');
        var original = [], names = [], rot = 0, spinning = false, hl = -1, flick = 0, lastIdx = -1;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function parse(t) { return t.split(/\r?\n/).map(function (s) { return s.trim(); }).filter(Boolean); }

        function sizeCanvas() {
            var dpr = window.devicePixelRatio || 1, w = canvas.clientWidth;
            canvas.width = w * dpr; canvas.height = w * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            draw();
        }

        function colorFor(i, n) {
            var c = i % COLORS.length;
            if (i === n - 1 && n > 1 && c === 0) c = 3;
            return COLORS[c];
        }

        function draw() {
            var w = canvas.clientWidth, cx = w / 2, cy = w / 2, R = w / 2 - 4, ring = Math.max(14, w * 0.034), r = R - ring, n = names.length;
            ctx.clearRect(0, 0, w, w);

            // outer brass rim
            var g = ctx.createLinearGradient(0, 0, w, w);
            g.addColorStop(0, '#e6cf9c'); g.addColorStop(.5, '#a98440'); g.addColorStop(1, '#d9bd82');
            ctx.beginPath(); ctx.arc(cx, cy, R, 0, TAU); ctx.fillStyle = g; ctx.fill();

            if (!n) {
                ctx.beginPath(); ctx.arc(cx, cy, r, 0, TAU); ctx.fillStyle = '#eceef3'; ctx.fill();
                ctx.fillStyle = '#6b7088'; ctx.textAlign = 'center'; ctx.font = '20px Georgia, serif';
                ctx.fillText('Add names to begin', cx, cy + 6);
                return;
            }
            var a = TAU / n;
            ctx.save(); ctx.translate(cx, cy); ctx.rotate(rot);

            for (var i = 0; i < n; i++) {
                ctx.beginPath(); ctx.moveTo(0, 0); ctx.arc(0, 0, r, i * a, (i + 1) * a); ctx.closePath();
                ctx.fillStyle = colorFor(i, n); ctx.fill();
                var sh = ctx.createRadialGradient(0, 0, r * 0.15, 0, 0, r);   // soft depth toward the rim
                sh.addColorStop(0, 'rgba(255,255,255,.14)'); sh.addColorStop(1, 'rgba(0,0,0,.14)');
                ctx.fillStyle = sh; ctx.fill();
                ctx.lineWidth = 1.5; ctx.strokeStyle = 'rgba(255,255,255,.7)'; ctx.stroke();

                ctx.save(); ctx.rotate(i * a + a / 2);
                ctx.fillStyle = '#fff'; ctx.textAlign = 'right'; ctx.textBaseline = 'middle';
                var fs = Math.max(11, Math.min(26, r * a * 0.4));
                ctx.font = '600 ' + fs + 'px "Palatino Linotype", Palatino, Georgia, serif';
                var label = names[i], maxW = r * 0.72;
                while (ctx.measureText(label).width > maxW && label.length > 3) label = label.slice(0, -2);
                if (label !== names[i]) label = label.trimEnd() + '\u2026';
                ctx.fillText(label, r - 22, 0);
                ctx.restore();
            }

            if (hl >= 0 && hl < n) {   // highlight the selected slice
                ctx.beginPath(); ctx.moveTo(0, 0); ctx.arc(0, 0, r - 1, hl * a, (hl + 1) * a); ctx.closePath();
                ctx.lineWidth = 5; ctx.strokeStyle = '#e6cf9c'; ctx.stroke();
            }

            // pegs on the rim
            for (var k = 0; k < n; k++) {
                var ang = k * a, px = Math.cos(ang) * (r + ring / 2), py = Math.sin(ang) * (r + ring / 2);
                ctx.beginPath(); ctx.arc(px, py, Math.max(2.2, ring * 0.2), 0, TAU); ctx.fillStyle = '#fff8e6'; ctx.fill();
            }
            ctx.restore();

            // inner rim line and hub
            ctx.beginPath(); ctx.arc(cx, cy, r, 0, TAU); ctx.lineWidth = 2; ctx.strokeStyle = '#fff'; ctx.stroke();
            var hub = Math.max(22, w * 0.05);
            var hg = ctx.createRadialGradient(cx - hub * .3, cy - hub * .3, 2, cx, cy, hub);
            hg.addColorStop(0, '#f1dfb4'); hg.addColorStop(1, '#a98440');
            ctx.beginPath(); ctx.arc(cx, cy, hub, 0, TAU); ctx.fillStyle = hg; ctx.fill();
            ctx.lineWidth = 3; ctx.strokeStyle = '#fff'; ctx.stroke();
            ctx.beginPath(); ctx.arc(cx, cy, hub * .32, 0, TAU); ctx.fillStyle = '#1d2340'; ctx.fill();
        }

        function winnerIndex() {
            var a = TAU / names.length, p = (((-Math.PI / 2 - rot) % TAU) + TAU) % TAU;
            return Math.floor(p / a) % names.length;
        }

        function updateUI() {
            document.getElementById('count').textContent = names.length + (names.length === 1 ? ' name' : ' names');
            spinBtn.disabled = spinning || names.length < 2;
        }

        function finish() {
            spinning = false; stopMusic(); fanfare();
            var i = winnerIndex(), who = names[i];
            hl = i; draw();
            document.getElementById('wlabel').textContent = 'Selected';
            var el = document.getElementById('wname'); el.textContent = who;
            el.classList.remove('show'); void el.offsetWidth; el.classList.add('show');
            var li = document.createElement('li'); li.textContent = who;
            document.getElementById('history').appendChild(li);
            if (document.getElementById('removeAfter').checked) {
                setTimeout(function () { if (!spinning) { names.splice(i, 1); hl = -1; ta.value = names.join('\n'); draw(); updateUI(); } }, 1400);
            }
            updateUI();
        }

        // ---- Sound: built with Web Audio, so no audio files are needed ----
        var AC = window.AudioContext || window.webkitAudioContext, ac = null, master = null;
        var customEl = null, musicTimer = null, step = 0, prog = 0;
        var NOTES = [261.63, 293.66, 329.63, 392.0, 440.0, 523.25, 587.33, 659.25];   // C major pentatonic
        var TUNE = [0, 2, 4, 5, 4, 2, 3, 1, 0, 2, 4, 7, 6, 4, 5, 3];

        function soundOn() { return document.getElementById('sound').checked; }
        function ensureAudio() {
            if (!AC) return false;
            if (!ac) { ac = new AC(); master = ac.createGain(); master.gain.value = 0.28; master.connect(ac.destination); }
            if (ac.state === 'suspended') ac.resume();
            return true;
        }
        function tone(freq, dur, type, vol, delay) {
            var t = ac.currentTime + (delay || 0), o = ac.createOscillator(), g = ac.createGain();
            o.type = type; o.frequency.value = freq;
            g.gain.setValueAtTime(0.0001, t);
            g.gain.exponentialRampToValueAtTime(vol, t + 0.015);
            g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
            o.connect(g); g.connect(master); o.start(t); o.stop(t + dur + 0.05);
        }
        function tick() { if (ac && soundOn()) tone(1500, 0.035, 'square', 0.035); }
        function nextNote() {
            var n = TUNE[step % TUNE.length];
            tone(NOTES[n], 0.32, 'triangle', 0.22);
            if (step % 4 === 0) tone(NOTES[n] / 2, 0.5, 'sine', 0.2);        // soft bass on each bar
            step++;
            musicTimer = setTimeout(nextNote, 150 + 330 * prog);           // slows down with the wheel
        }
        function startMusic() {
            if (!soundOn()) return;
            if (customEl) { customEl.currentTime = 0; customEl.play().catch(function () { }); return; }
            if (!ensureAudio()) return;
            step = 0; prog = 0; nextNote();
        }
        function stopMusic() {
            clearTimeout(musicTimer); musicTimer = null;
            if (customEl) customEl.pause();
        }
        function fanfare() {
            if (!soundOn() || !ensureAudio()) return;
            [523.25, 659.25, 783.99, 1046.5].forEach(function (f, i) { tone(f, 0.55, 'triangle', 0.22, i * 0.11); });
            tone(1046.5, 0.9, 'sine', 0.16, 0.45);
        }
        // Optional: play your own track instead — window.setWheelMusic('/assets/audio/spin.mp3')
        window.setWheelMusic = function (url) { customEl = url ? new Audio(url) : null; if (customEl) customEl.loop = true; };

        function spin() {
            if (spinning || names.length < 2) return;
            if (soundOn()) ensureAudio();   // must start from a click
            spinning = true; hl = -1; updateUI();
            document.getElementById('wlabel').textContent = ''; document.getElementById('wname').textContent = '';
            var start = rot, total = TAU * (5 + Math.random() * 4);
            if (reduceMotion) { rot = start + total; finish(); return; }
            var dur = 5200, t0 = performance.now(); lastIdx = winnerIndex();
            startMusic();
            (function frame(now) {
                var t = Math.min(1, (now - t0) / dur);
                rot = start + total * (1 - Math.pow(1 - t, 4));
                var idx = winnerIndex();
                prog = t;
                if (idx !== lastIdx) { lastIdx = idx; flick = 1; tick(); }
                flick *= 0.82;
                pointer.style.transform = 'rotate(' + (-flick * 16) + 'deg)';
                draw();
                if (t < 1) requestAnimationFrame(frame); else { pointer.style.transform = ''; finish(); }
            })(t0);
        }

        function setNames(list) {
            original = list.slice(); names = list.slice(); ta.value = names.join('\n'); hl = -1;
            document.getElementById('history').innerHTML = '';
            document.getElementById('wlabel').textContent = ''; document.getElementById('wname').textContent = '';
            draw(); updateUI();
        }

        ta.addEventListener('input', function () { if (!spinning) { names = parse(ta.value); hl = -1; draw(); updateUI(); } });
        spinBtn.addEventListener('click', spin);
        document.getElementById('sound').addEventListener('change', function () { if (!this.checked) stopMusic(); else if (spinning) startMusic(); });
        canvas.addEventListener('click', spin);
        document.getElementById('shuffle').addEventListener('click', function () {
            if (spinning) return;
            for (var i = names.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)), x = names[i]; names[i] = names[j]; names[j] = x; }
            hl = -1; ta.value = names.join('\n'); draw();
        });
        document.getElementById('reset').addEventListener('click', function () { if (!spinning) setNames(original); });
        window.addEventListener('resize', sizeCanvas);

        // From your LMS: window.setWheelNames(['Dela Cruz, Juan', 'Santos, Maria'])
        var students = <?= json_encode($students, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;

        window.setWheelNames = setNames;
        console.log(students);
        setNames(students);
        //setNames(['Student 1', 'Student 2', 'Student 3', 'Student 4', 'Student 5', 'Student 6', 'Student 7', 'Student 8']);
        sizeCanvas();
    })();
</script>

<?= $this->endSection() ?>