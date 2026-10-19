
<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>


<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Classes</h4>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Classes</div>
                <div class="fs-3 fw-bold"><?= esc($stats['classes']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Student Placements</div>
                <div class="fs-3 fw-bold"><?= esc($stats['students_placed']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sections With Classes</div>
                <div class="fs-3 fw-bold"><?= esc($stats['sections_covered']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Create class -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-semibold">Create a Class</div>
            <div class="card-body">
                <form action="<?= route_to('class.store') ?>" method="post" id="createClassForm">
                    <?= csrf_field() ?>


                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <select name="section_id" id="section_id" class="form-control" required>
                            <option value="">Select section…</option>
                            <?php foreach ($sections as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= old('section_id') == $s['id'] ? 'selected' : '' ?>>
                                    <?= esc(trim(($s['grade_name'] ?? '') . ' - ' . $s['name'], ' -')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-muted small">(offered in the
                                section)</span></label>
                        <select name="subject_id" id="subject_id" class="form-control" required disabled>
                            <option value="">Select a section first</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject Teacher <span class="text-muted small">(handled for the
                                class)</span></label>
                        <select name="subject_teacher_id" id="subject_teacher_id" class="form-control" required
                            disabled>
                            <option value="">Select a subject first</option>
                        </select>
                    </div>

                    <div class="alert alert-info py-2 small d-none" id="enrolleeInfo"></div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                        Create Class &amp; Add Students
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Classes table -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">All Classes</div>
            <div class="card-body">
                <table id="classesTable" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Section</th>
                            <th>School Year</th>
                            <th>Students</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $c): ?>
                            <tr>
                                <td><?= esc($c['subject_name']) ?></td>
                                <td><a
                                        href="<?= route_to('view.class.details') . '/?id=' . esc($c['id']) ?>"><?= esc(trim(($c['grade_name'] ?? '') . ' - ' . $c['section_name'], ' -')) ?></a>
                                </td>
                                <td><?= esc($c['school_year']) ?></td>
                                <td><span class="badge bg-primary"><?= esc($c['student_count']) ?></span></td>
                                <td>
                                    <form action="<?= base_url('admin/class-sync/' . $c['id']) ?>" method="post"
                                        class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-secondary"
                                            title="Add students who enrolled in the section after this class was created">
                                            Sync Students
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        $('#classesTable').DataTable();

        <?php if (session()->getFlashdata('success')): ?>
            toastr.success(<?= json_encode(session()->getFlashdata('success')) ?>);
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            toastr.error(<?= json_encode(session()->getFlashdata('error')) ?>);
        <?php endif; ?>

        const $section = $('#section_id'), $subject = $('#subject_id'),
            $year = $('#school_year'), $info = $('#enrolleeInfo'), $btn = $('#submitBtn'), $teachers = $('#subject_teacher_id');

        function loadTeachers() {
            $.getJSON('<?= route_to('teachers.list') ?>', function (res) {
                if (!res.teachers.length) {
                    $teachers.prop('disabled', true).html('<option value="">No available teachers</option>');
                    return;
                }
                let opts = '<option value="">Select teacher…</option>';
                res.teachers.forEach(t => opts += `<option value="${t.id}">${$('<div>').text(t.teacher).html()}</option>`);
                $teachers.prop('disabled', false).html(opts);

            });

        }
        function loadSubjects() {
            const sectionId = $section.val();
            $info.addClass('d-none');
            $btn.prop('disabled', true);
            if (!sectionId) {
                $subject.prop('disabled', true).html('<option value="">Select a section first</option>');
                return;
            }

            $.getJSON('<?= route_to('grade.level.subjects') ?>/?id=' + sectionId,
                { school_year: $year.val() }, function (res) {
                    if (!res.subjects.length) {
                        $subject.prop('disabled', true).html('<option value="">No available subjects for this section</option>');
                        return;
                    }
                    let opts = '<option value="">Select subject…</option>';
                    res.subjects.forEach(s => opts += `<option value="${s.id}">${$('<div>').text(s.name).html()}</option>`);
                    $subject.prop('disabled', false).html(opts);

                    $info.removeClass('d-none')
                        .text(res.enrollees + ' enrolled student(s) in this section will be added automatically.');
                });
        }

        $section.on('change', loadSubjects);
        $subject.on('change', loadTeachers);
        $teachers.on('change', function () { $btn.prop('disabled', !$(this).val()); });
    });
</script>
<?= $this->endSection() ?>