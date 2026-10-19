<?= $this->extend('backend/layout/pages-student-layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-12">
            <div class="title">
                <h4>My Quizzes</h4>
            </div>
        </div>
    </div>
</div>

<div class="card-box mb-30 pd-20">
    <?php if (!$quizzes): ?>
        <p class="text-muted mb-0">There are no quizzes for you right now.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Quiz</th>
                        <th>Questions</th>
                        <th>Time limit</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q):
                        $status = $q['status']; ?>
                        <tr>
                            <td><?= esc($q['subject']) ?></td>
                            <td><strong><?= esc($quizTypes[$q['quiz_type']] ?? $q['quiz_type']) ?></strong>
                                <br><small class="text-muted">Term <?= (int) $q['term'] ?> &middot;
                                    <?= esc($q['quizcode']) ?></small>
                            </td>
                            <td><?= (int) $q['total'] ?></td>
                            <td><?= $q['time_limit'] ? (int) $q['time_limit'] . ' min' : 'None' ?></td>
                            <td>
                                <?php if ($status === 'in_progress'): ?><span class="badge badge-warning">In progress</span>
                                <?php elseif ($status): ?><span class="badge badge-success">Submitted</span>
                                <?php else: ?><span class="badge badge-info">Open</span><?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-sm btn-primary" href="<?= route_to('student.quiz.intro', $q['id']) ?>">
                                    <?= $status === 'in_progress' ? 'Resume' : ($status ? 'View' : 'Open') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>