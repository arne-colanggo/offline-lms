<?= $this->extend('backend/layout/pages-student-layout') ?>

<?= $this->section('content') ?>
<?php
$status = $attempt['status'] ?? null;
$done = $status && $status !== 'in_progress';
?>
<div class="page-header">
    <div class="row">
        <div class="col-md-8">
            <div class="title">
                <h4><?= esc($quizTypes[$quiz['quiz_type']] ?? $quiz['quiz_type']) ?>: <?= esc($subject) ?></h4>
                <small class="text-muted">Term <?= (int) $quiz['term'] ?> &middot; <?= esc($quiz['quizcode']) ?></small>
            </div>
        </div>
        <div class="col-md-4 text-md-right"><a href="<?= esc($listUrl) ?>" class="btn btn-outline-secondary">My
                quizzes</a></div>
    </div>
</div>

<div class="card-box mb-30 pd-20">
    <?php if ($done): ?>
        <h5>Quiz submitted</h5>
        <?php if ($status === 'auto_submitted'): ?>
            <p class="text-warning">
                <?= ($attempt['auto_reason'] ?? '') === 'time_up' ? 'Your time ran out, so the quiz was submitted automatically.'
                    : (($attempt['auto_reason'] ?? '') === 'too_many_violations' ? 'The quiz was submitted automatically after too many warnings.'
                        : 'Your teacher submitted this quiz for you.') ?>
            </p>
        <?php endif; ?>
        <?php if ($quiz['show_answer'] === 'T'): ?>
            <p class="font-18">Score: <strong><?= rtrim(rtrim(number_format((float) $attempt['score'], 2), '0'), '.') ?> /
                    <?= (int) $total ?></strong></p>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Question</th>
                            <th>Your answer</th>
                            <th>Correct answer</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($review as $r): ?>
                            <tr>
                                <td><?= $r['no'] ?></td>
                                <td style="white-space:normal"><?= esc($r['question']) ?></td>
                                <td><?= esc($r['yours']) ?></td>
                                <td><?= esc($r['correct']) ?></td>
                                <td><?= $r['score'] === null ? 'Pending' : rtrim(rtrim(number_format((float) $r['score'], 2), '0'), '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Your teacher will share your results.</p>
        <?php endif; ?>

    <?php elseif (!$attempt && $quiz['isactive'] !== 'T'): ?>
        <p class="mb-0">This quiz is not open yet.</p>

    <?php else: ?>
        <p class="font-16"><?= (int) $total ?> questions
            <?= $quiz['time_limit'] ? '&middot; ' . (int) $quiz['time_limit'] . ' minutes' : '&middot; no time limit' ?>
        </p>
        <h6>Before you start</h6>
        <ul>
            <li>The quiz opens in <strong>full screen</strong>. Stay in the quiz until you submit.</li>
            <li>Switching tabs or windows, leaving full screen, copying, pasting and opening developer tools are
                <strong>recorded and your teacher sees them live</strong>.
            </li>
            <?php if ((int) $quiz['max_violations'] > 0): ?>
                <li>After <strong><?= (int) $quiz['max_violations'] ?> warnings</strong> your quiz is submitted automatically.
                </li>
            <?php endif; ?>
            <li>Your answers save automatically. If your connection drops you can resume, but the timer keeps running.</li>
            <li>You can open the quiz on only one tab or device at a time.</li>
            <li>You can only take this quiz once.</li>
        </ul>
        <a href="<?= esc($takeUrl) ?>" class="btn btn-primary btn-lg"><?= $attempt ? 'Resume quiz' : 'Start quiz' ?></a>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>