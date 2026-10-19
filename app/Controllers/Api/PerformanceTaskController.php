<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\AuthenticationServices;
use App\Models\PerformanceTaskModel;
use App\Models\PerformanceTaskScoreModel;
use App\Models\PerformanceTaskSubmissionModel;
use App\Models\RubricCriterionModel;
use App\Models\RubricDescriptorModel;
use App\Models\RubricLevelModel;
use App\Models\RubricModel;

/**
 * Performance Tasks + Rubrics.
 *
 * ASSUMPTIONS (adjust to your schema, all isolated in classOptions() / classStudents()):
 *   classes:  id, subject_id, section_id
 *   students: lrn, firstname, lastname, section_id
 */
class PerformanceTaskController extends BaseController
{
    protected $db;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // The auth filter has no role check, so block students here.
    private function isStaff(): bool
    {
        $u = session()->get('userdata');
        return $u && in_array($u['role'] ?? '', ['admin', 'teacher'], true);
    }

    private function deny()
    {
        return $this->response->setStatusCode(403)->setJSON(['status' => 0, 'message' => 'Not allowed.']);
    }

    // ---------------------------------------------------------------- helpers
    private function classOptions(): array
    {
        return $this->db->table('classes c')
            ->select("c.id, c.name AS label", false)
            ->where('c.schoolyearid', get_settings()->schoolyear)
            ->where('c.teacherid', get_user()->userdata['id'])
            ->orderBy('label')->get()->getResultArray();
    }

    private function classStudents(int $classId): array
    {
        return $this->db->table('class_students cs')
            ->select("st.lrn, CONCAT(p.lastname, ', ', p.firstname) AS name", false)
            ->join('students st', 'st.id = cs.studentid')
            ->join('profiles p', 'p.id = st.profile_id')
            ->join('classes c', 'c.id = cs.classid')
            ->where('c.id', $classId)
            ->orderBy('p.lastname')->get()->getResultArray();
    }

    private function loadRubric(int $id): ?array
    {
        $rubric = (new RubricModel())->find($id);
        if (!$rubric) {
            return null;
        }
        $rubric['levels'] = (new RubricLevelModel())->where('rubric_id', $id)->orderBy('ordering')->findAll();
        $rubric['criteria'] = (new RubricCriterionModel())->where('rubric_id', $id)->orderBy('ordering')->findAll();

        $cells = $this->db->table('rubric_descriptors d')
            ->select('d.criterion_id, d.level_id, d.description')
            ->join('rubric_criteria c', 'c.id = d.criterion_id')
            ->where('c.rubric_id', $id)->get()->getResultArray();
        $map = [];
        foreach ($cells as $c) {
            $map[$c['criterion_id']][$c['level_id']] = $c['description'];
        }
        foreach ($rubric['criteria'] as &$crit) {
            $crit['descriptors'] = [];
            foreach ($rubric['levels'] as $lv) {
                $crit['descriptors'][] = $map[$crit['id']][$lv['id']] ?? '';
            }
        }
        return $rubric;
    }

    private function rubricMax(array $rubric): float
    {
        $top = 0;
        foreach ($rubric['levels'] as $l) {
            $top = max($top, (float) $l['points']);
        }
        $w = 0;
        foreach ($rubric['criteria'] as $c) {
            $w += (float) $c['weight'];
        }
        return $top * $w;
    }

    // ---------------------------------------------------------------- RUBRICS
    public function rubrics()
    {
        $id = $this->request->getVar('class_id');
        if (!$this->isStaff()) {
            return redirect()->to(route_to('unauthorized'));
        }
        $rows = $this->db->table('rubrics r')
            ->select('r.*, (SELECT COUNT(*) FROM rubric_criteria WHERE rubric_id = r.id) AS criteria_count,
                      (SELECT COUNT(*) FROM performance_tasks WHERE rubric_id = r.id) AS used_count', false)
            ->orderBy('r.name')->get()->getResultArray();

        return view('backend/pages/teacher/rubrics', ['pageTitle' => 'Rubrics', 'rubrics' => $rows, 'classId' => $id]);
    }

    public function rubricForm($id = null)
    {
        $classid = $this->request->getVar('class_id');
        if (!$this->isStaff()) {
            return redirect()->to(route_to('unauthorized'));
        }
        $rubric = $id ? $this->loadRubric((int) $id) : null;
        if ($id && !$rubric) {
            return redirect()->to(route_to('get.rubrics'));
        }
        $inUse = $id ? $this->db->table('performance_tasks')->where('rubric_id', $id)->countAllResults() > 0 : false;

        // Starter template for a new rubric
        $data = $rubric ?? [
            'id' => null,
            'name' => '',
            'description' => '',
            'levels' => [
                ['label' => 'Excellent', 'points' => 4],
                ['label' => 'Proficient', 'points' => 3],
                ['label' => 'Developing', 'points' => 2],
                ['label' => 'Beginning', 'points' => 1],
            ],
            'criteria' => [['name' => '', 'weight' => 1, 'descriptors' => ['', '', '', '']]],
        ];

        return view('backend/pages/teacher/rubric_form', [
            'pageTitle' => $id ? 'Edit Rubric' : 'New Rubric',
            'rubric' => $data,
            'inUse' => $inUse,
            'classId' => $classid
        ]);
    }

    public function saveRubric()
    {
        if (!$this->isStaff()) {
            return $this->deny();
        }
        $in = $this->request->getJSON(true) ?? [];
        $id = (int) ($in['id'] ?? 0);

        $name = trim($in['name'] ?? '');
        $levels = $in['levels'] ?? [];
        $criteria = $in['criteria'] ?? [];
        $class_id = $in['class_id'] ?? [];
        $inUse = $id && $this->db->table('performance_tasks')->where('rubric_id', $id)->countAllResults() > 0;

        if ($name === '') {
            return $this->response->setJSON(['status' => 0, 'message' => 'Rubric name is required.']);
        }
        if (!$inUse) {
            if (count($levels) < 2 || count($criteria) < 1) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Add at least 2 levels and 1 criterion.']);
            }
            foreach ($levels as $l) {
                if (trim($l['label'] ?? '') === '' || !is_numeric($l['points'] ?? null)) {
                    return $this->response->setJSON(['status' => 0, 'message' => 'Every level needs a label and numeric points.']);
                }
            }
            foreach ($criteria as $c) {
                if (trim($c['name'] ?? '') === '' || !is_numeric($c['weight'] ?? null) || $c['weight'] <= 0) {
                    return $this->response->setJSON(['status' => 0, 'message' => 'Every criterion needs a name and a weight above 0.']);
                }
            }
        }

        $this->db->transStart();
        $rubrics = new RubricModel();
        $head = ['name' => $name, 'description' => trim($in['description'] ?? ''), 'class_id' => $class_id];

        if ($id) {
            $rubrics->update($id, $head);
        } else {
            $head['created_by'] = AuthenticationServices::id();
            $id = $rubrics->insert($head);
        }

        // Structure is locked once a task uses the rubric (scores reference its rows).
        if (!$inUse) {
            $this->db->table('rubric_criteria')->where('rubric_id', $id)->delete(); // cascades descriptors
            $this->db->table('rubric_levels')->where('rubric_id', $id)->delete();

            $levelIds = [];
            foreach (array_values($levels) as $i => $l) {
                $this->db->table('rubric_levels')->insert([
                    'rubric_id' => $id,
                    'label' => trim($l['label']),
                    'points' => $l['points'],
                    'ordering' => $i,
                ]);
                $levelIds[$i] = $this->db->insertID();
            }
            foreach (array_values($criteria) as $j => $c) {
                $this->db->table('rubric_criteria')->insert([
                    'rubric_id' => $id,
                    'name' => trim($c['name']),
                    'weight' => $c['weight'],
                    'ordering' => $j,
                ]);
                $critId = $this->db->insertID();
                foreach ($levelIds as $i => $lvId) {
                    $this->db->table('rubric_descriptors')->insert([
                        'criterion_id' => $critId,
                        'level_id' => $lvId,
                        'description' => trim($c['descriptors'][$i] ?? ''),
                    ]);
                }
            }
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Could not save the rubric.']);
        }
        return $this->response->setJSON(['status' => 1, 'message' => 'Rubric saved.', 'redirect' => route_to('get.rubrics')]);
    }

    public function deleteRubric($id)
    {
        if (!$this->isStaff()) {
            return $this->deny();
        }
        if ($this->db->table('performance_tasks')->where('rubric_id', $id)->countAllResults() > 0) {
            return $this->response->setJSON(['status' => 0, 'message' => 'This rubric is used by a performance task and cannot be deleted.']);
        }
        (new RubricModel())->delete($id);
        return $this->response->setJSON(['status' => 1, 'message' => 'Rubric deleted.']);
    }

    // ------------------------------------------------------- PERFORMANCE TASKS
    public function index()
    {
        if (!$this->isStaff()) {
            return redirect()->to(route_to('unauthorized'));
        }
        $classId = (int) $this->request->getGet('class_id');

        $q = $this->db->table('performance_tasks t')
            ->select("t.*, r.name AS rubric_name, CONCAT(s.name,' - ',sec.name) AS class_name,
                      (SELECT COUNT(*) FROM performance_task_submissions WHERE task_id = t.id) AS scored_count", false)
            ->join('rubrics r', 'r.id = t.rubric_id')
            ->join('classes c', 'c.id = t.class_id')
            ->join('subjects s', 's.id = c.subjectid')
            ->join('sections sec', 'sec.id = c.sectionid')
            ->orderBy('t.created_at', 'DESC');
        if ($classId) {
            $q->where('t.class_id', $classId);
        }

        return view('backend/pages/teacher/performancetask', [
            'pageTitle' => 'Performance Tasks',
            'tasks' => $q->get()->getResultArray(),
            'classes' => $this->classOptions(),
            'classId' => $classId,
        ]);
    }

    public function taskForm($id = null)
    {
        if (!$this->isStaff()) {
            return redirect()->to(route_to('unauthorized'));
        }
        $task = $id ? (new PerformanceTaskModel())->find($id) : null;
        if ($id && !$task) {
            return redirect()->to(route_to('get.performancetasks'));
        }
        return view('backend/pages/teacher/task_form', [
            'pageTitle' => $id ? 'Edit Performance Task' : 'New Performance Task',
            'task' => $task,
            'classes' => $this->classOptions(),
            'rubrics' => (new RubricModel())->orderBy('name')->findAll(),
            'classId' => (int) $this->request->getGet('class_id'),
        ]);
    }

    public function saveTask()
    {
        if (!$this->isStaff()) {
            return $this->deny();
        }
        $id = (int) $this->request->getPost('id');

        $rules = [
            'title' => 'required|max_length[200]',
            'class_id' => 'required|is_natural_no_zero',
            'rubric_id' => 'required|is_natural_no_zero',
            'term' => 'required|in_list[1,2,3,4]',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 0, 'message' => implode(' ', $this->validator->getErrors())]);
        }

        $data = [
            'class_id' => $this->request->getPost('class_id'),
            'bow_details_id' => $this->request->getPost('bow_details_id') ?: null,
            'rubric_id' => $this->request->getPost('rubric_id'),
            'title' => trim($this->request->getPost('title')),
            'instructions' => $this->request->getPost('instructions'),
            'term' => $this->request->getPost('term'),
            'due_date' => $this->request->getPost('due_date') ?: null,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ];

        $tasks = new PerformanceTaskModel();
        if ($id) {
            // Changing the rubric after scoring would orphan scores.
            $scored = $this->db->table('performance_task_submissions')->where('task_id', $id)->countAllResults();
            $old = $tasks->find($id);
            if ($scored && $old && (int) $old['rubric_id'] !== (int) $data['rubric_id']) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Students are already scored; the rubric cannot be changed.']);
            }
            $tasks->update($id, $data);
        } else {
            $data['created_by'] = AuthenticationServices::id();
            $tasks->insert($data);
        }
        return $this->response->setJSON(['status' => 1, 'message' => 'Performance task saved.', 'redirect' => route_to('get.performancetasks')]);
    }

    public function deleteTask($id)
    {
        if (!$this->isStaff()) {
            return $this->deny();
        }
        (new PerformanceTaskModel())->delete($id); // submissions + scores cascade
        return $this->response->setJSON(['status' => 1, 'message' => 'Performance task deleted.']);
    }

    // ---------------------------------------------------------------- SCORING
    public function scoreForm($taskId)
    {
        if (!$this->isStaff()) {
            return redirect()->to(route_to('unauthorized'));
        }
        $task = (new PerformanceTaskModel())->find($taskId);
        if (!$task) {
            return redirect()->to(route_to('get.performancetasks'));
        }
        $rubric = $this->loadRubric((int) $task['rubric_id']);

        return view('backend/pages/teacher/score', [
            'pageTitle' => 'Score: ' . $task['title'],
            'task' => $task,
            'rubric' => $rubric,
            'max' => $this->rubricMax($rubric),
            'students' => $this->classStudents((int) $task['class_id']),
            'scored' => array_column(
                $this->db->table('performance_task_submissions')->select('lrn, total_score, max_score')
                    ->where('task_id', $taskId)->get()->getResultArray(),
                null,
                'lrn'
            ),
        ]);
    }

    public function studentScore($taskId, $lrn)
    {
        if (!$this->isStaff()) {
            return $this->deny();
        }
        $sub = (new PerformanceTaskSubmissionModel())->where(['task_id' => $taskId, 'lrn' => $lrn])->first();
        $levels = [];
        if ($sub) {
            foreach ((new PerformanceTaskScoreModel())->where('submission_id', $sub['id'])->findAll() as $s) {
                $levels[$s['criterion_id']] = (int) $s['level_id'];
            }
        }
        return $this->response->setJSON(['feedback' => $sub['feedback'] ?? '', 'levels' => $levels]);
    }

    public function saveScore($taskId)
    {
        if (!$this->isStaff()) {
            return $this->deny();
        }
        $task = (new PerformanceTaskModel())->find($taskId);
        $in = $this->request->getJSON(true) ?? [];
        $lrn = trim($in['lrn'] ?? '');

        if (!$task || $lrn === '') {
            return $this->response->setJSON(['status' => 0, 'message' => 'Select a student first.']);
        }
        // Only students of this class can be scored.
        if (!in_array($lrn, array_column($this->classStudents((int) $task['class_id']), 'lrn'), true)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Student is not in this class.']);
        }

        // Scores are recomputed on the server from the rubric; client totals are ignored.
        $rubric = $this->loadRubric((int) $task['rubric_id']);
        $levels = array_column($rubric['levels'], null, 'id');
        $rows = [];
        $total = 0;
        foreach ($rubric['criteria'] as $c) {
            $lvId = (int) ($in['levels'][$c['id']] ?? 0);
            if (!isset($levels[$lvId])) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Rate every criterion before saving.']);
            }
            $score = (float) $levels[$lvId]['points'] * (float) $c['weight'];
            $total += $score;
            $rows[] = ['criterion_id' => $c['id'], 'level_id' => $lvId, 'score' => $score];
        }

        $this->db->transStart();
        $subs = new PerformanceTaskSubmissionModel();
        $existing = $subs->where(['task_id' => $taskId, 'lrn' => $lrn])->first();
        $head = [
            'total_score' => $total,
            'max_score' => $this->rubricMax($rubric),
            'feedback' => trim($in['feedback'] ?? ''),
            'scored_by' => AuthenticationServices::id(),
        ];
        if ($existing) {
            $subId = $existing['id'];
            $subs->update($subId, $head);
            $this->db->table('performance_task_scores')->where('submission_id', $subId)->delete();
        } else {
            $subId = $subs->insert($head + ['task_id' => $taskId, 'lrn' => $lrn]);
        }
        foreach ($rows as $r) {
            $this->db->table('performance_task_scores')->insert($r + ['submission_id' => $subId]);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Could not save the score.']);
        }
        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Score saved.',
            'total' => $total,
            'max' => $this->rubricMax($rubric),
        ]);
    }
}