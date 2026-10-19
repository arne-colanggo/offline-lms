<?= $this->extend('backend/layout/pages-layout') ?>

<?php $t = $task ?? [];
$sel = $t['class_id'] ?? $classId; ?>

<?= $this->section('content') ?>
<div class="card-box mb-30 pd-20">
    <h4 class="text-blue h4 mb-3"><?= esc($pageTitle) ?></h4>
    <form id="taskForm">
        <input type="hidden" name="id" value="<?= $t['id'] ?? '' ?>">
        <div class="row">
            <div class="col-md-6 form-group"><label>Title</label>
                <input type="text" name="title" class="form-control" maxlength="200"
                    value="<?= esc($t['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 form-group"><label>Class</label>
                <select name="class_id" class="form-control" required>
                    <option value="">Select class</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $sel == $c['id'] ? 'selected' : '' ?>><?= esc($c['label']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 form-group"><label>Rubric</label>
                <select name="rubric_id" class="form-control" required>
                    <option value="">Select rubric</option>
                    <?php foreach ($rubrics as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($t['rubric_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                            <?= esc($r['name']) ?></option>
                    <?php endforeach ?>
                </select>
                <small><a href="<?= route_to('get.rubric.new') ?>">Create a rubric</a></small>
            </div>
            <div class="col-md-2 form-group"><label>Term</label>
                <select name="term" class="form-control">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <option value="<?= $i ?>" <?= ($t['term'] ?? 1) == $i ? 'selected' : '' ?>>Term <?= $i ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div class="col-md-3 form-group"><label>Due date</label>
                <input type="date" name="due_date" class="form-control" value="<?= esc($t['due_date'] ?? '') ?>">
            </div>
            <div class="col-md-3 form-group"><label>BOW competency ID (optional)</label>
                <input type="number" name="bow_details_id" class="form-control"
                    value="<?= esc($t['bow_details_id'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group"><label>Instructions</label>
            <textarea name="instructions" class="form-control" rows="5"><?= esc($t['instructions'] ?? '') ?></textarea>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="pub" name="is_published" value="1"
                <?= !empty($t['is_published']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="pub">Published</label>
        </div>
        <button class="btn btn-primary" type="submit">Save</button>
        <a href="<?= route_to('get.performancetasks') ?>" class="btn btn-light">Cancel</a>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#taskForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post('<?= route_to('post.performancetask') ?>', $(this).serialize(), function (r) {
            r.status ? (toastr.success(r.message), setTimeout(() => location.href = r.redirect, 600)) : (toastr.error(r.message), btn.prop('disabled', false));
        }, 'json').fail(() => { toastr.error('Request failed.'); btn.prop('disabled', false); });
    });
</script>
<?= $this->endSection() ?>