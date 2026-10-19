<?php

namespace App\Controllers\Api;

use App\Libraries\QuizAttemptService;
use App\Libraries\QuizGrader;
use App\Libraries\QuizRoster;

/**
 * Questions + quizzes managed from a Class page.
 * A class = a subject offered in a section. The teacher assigned to the class (or an admin)
 * can: write questions for that subject's BOW competencies, and build quizzes for that class.
 * Everything is checked against the class on every request.
 */
class ClassQuizController extends QuizController
{
    private QuizRoster $roster;
    private QuizAttemptService $svc;

    public function __construct()
    {
        parent::__construct();
        $this->roster = new QuizRoster();
        $this->svc = new QuizAttemptService();
    }

    // The classes-table mapping lives in App\Libraries\QuizRoster (adjust it there)
    private const CLASS_TABLE = QuizRoster::CLASS_TABLE;
    private const COL = QuizRoster::COL;
    private const CLASS_PAGE = 'admin/class/%d';   // your class details page URL, e.g. 'admin/class/?id=%d' (sprintf with the class id)

    /* ---------------------------------------------------------------- */

    /** Class with subject/section/year names, normalised to fixed keys. */
    private function classRow(int $classId): ?array
    {
        $hasYear = $this->db->fieldExists(self::COL['schoolyear'], self::CLASS_TABLE);

        $b = $this->db->table(self::CLASS_TABLE . ' c')
            ->select('c.*, s.name AS subject_name, sc.name AS section_name')
            ->join('subjects s', 's.id = c.' . self::COL['subject'], 'left')
            ->join('sections sc', 'sc.id = c.' . self::COL['section'], 'left');
        if ($hasYear) {
            $b->select('sy.name AS schoolyear_name')
                ->join('schoolyears sy', 'sy.id = c.' . self::COL['schoolyear'], 'left');
        }
        $row = $b->where('c.id', $classId)->get()->getRowArray();
        if (!$row) {
            return null;
        }

        $yearId = $hasYear ? (int) $row[self::COL['schoolyear']] : 0;
        $yearName = $row['schoolyear_name'] ?? null;
        if (!$yearId) {
            // No school year on the class: use the latest school year
            $y = $this->db->table('schoolyears')->orderBy('id', 'DESC')->get(1)->getRowArray();
            $yearId = (int) ($y['id'] ?? 0);
            $yearName = $y['name'] ?? null;
        }

        return [
            'id' => (int) $row['id'],
            'subjectid' => (int) $row[self::COL['subject']],
            'sectionid' => (int) $row[self::COL['section']],
            'schoolyearid' => $yearId,
            'teacherid' => (int) ($row[self::COL['teacher']] ?? 0),
            'subject_name' => $row['subject_name'],
            'section_name' => $row['section_name'],
            'schoolyear_name' => $yearName,
        ];
    }

    /** Admin, or the teacher assigned to this class. */
    private function canManage(array $class): bool
    {
        return $this->isAdmin()
            || ($this->role() === 'teacher' && $class['teacherid'] !== 0 && $class['teacherid'] === $this->userId());
    }

    /** @return array{0: ?array, 1: mixed} [class, error response] */
    private function authorize(int $classId, bool $json): array
    {
        $class = $this->classRow($classId);
        if (!$class) {
            return [null, $json ? $this->fail('Class not found.', 404) : redirect()->to(route_to('admin.dashboard'))];
        }
        if (!$this->canManage($class)) {
            return [null, $json ? $this->denyJson() : $this->denyPage()];
        }

        return [$class, null];
    }

    private function urls(int $cid): array
    {
        return [
            'list' => route_to('get.class.questions.list', $cid),
            'get' => $this->prefix('get.class.question', $cid),
            'save' => route_to('post.class.question', $cid),
            'remove' => $this->prefix('delete.class.question', $cid),
            'questionsPage' => route_to('get.class.questions', $cid),
            'quizzes' => route_to('get.class.quizzes', $cid),
            'quizSave' => route_to('post.class.quiz', $cid),
            'builderTpl' => $this->tpl('get.class.quiz.builder', $cid),
            'quizDelete' => $this->prefix('delete.class.quiz', $cid),
            'monitorTpl' => $this->tpl('get.class.quiz.monitor', $cid),
        ];
    }

    private const SENTINEL = '9999999';

    /** URL of an id route up to the id, e.g. ".../question/" (the JS appends the id). */
    private function prefix(string $route, int ...$args): string
    {
        return strstr(route_to($route, ...array_merge($args, [(int) self::SENTINEL])), self::SENTINEL, true);
    }

    /** URL of an id route with a {id} placeholder. */
    private function tpl(string $route, int ...$args): string
    {
        return str_replace(self::SENTINEL, '{id}', route_to($route, ...array_merge($args, [(int) self::SENTINEL])));
    }

    private function scope(array $class, array $extraUrls = []): array
    {
        return [
            'class' => $class,
            'backUrl' => base_url(sprintf(self::CLASS_PAGE, $class['id'])),
            'urls' => array_merge($this->urls($class['id']), $extraUrls),
        ];
    }

    private function questionInSubject(int $questionId, int $subjectId): bool
    {
        return $this->db->table('questions q')
            ->join('bow_details bd', 'bd.id = q.bowdetailsid')
            ->join('bows b', 'b.id = bd.bowid')
            ->where('q.id', $questionId)->where('b.subjectid', $subjectId)
            ->countAllResults() > 0;
    }

    private function quizInClass(int $quizId, int $classId): ?array
    {
        return $this->quizzes->where('id', $quizId)->where('classid', $classId)->first();
    }

    /* ==================== Questions ==================== */

    public function classQuestions(int $cid)
    {
        [$class, $err] = $this->authorize($cid, false);
        if ($err) {
            return $err;
        }

        return view('backend/pages/quiz/questions', [
            'pageTitle' => 'Class Questions',
            'scope' => $this->scope($class),
            'competencies' => $this->competencies($class['subjectid']),
            'optionTypes' => self::OPTION_TYPES,
            'taxonomy' => self::TAXONOMY,
        ]);
    }

    public function classQuestionList(int $cid)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }

        return $this->jsonOut(['data' => $this->questionRows($class['subjectid'])]);
    }

    public function classQuestionGet(int $cid, int $qid)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->questionInSubject($qid, $class['subjectid'])) {
            return $this->fail('Question not found in this subject.', 404);
        }

        $q = $this->questions->find($qid);
        $q['options'] = $this->options->where('questionid', $qid)->orderBy('id')->findAll();

        return $this->jsonOut(['status' => true, 'data' => $q]);
    }

    public function classQuestionSave(int $cid)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }

        $ids = array_map('intval', array_column($this->competencies($class['subjectid']), 'id'));

        return $this->storeQuestion($this->request->getJSON(true) ?? [], $ids);
    }

    public function classQuestionDelete(int $cid, int $qid)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->questionInSubject($qid, $class['subjectid'])) {
            return $this->fail('Question not found in this subject.', 404);
        }

        return $this->removeQuestion($qid);
    }

    /* ==================== Quizzes ==================== */

    public function classQuizzes(int $cid)
    {
        [$class, $err] = $this->authorize($cid, false);
        if ($err) {
            return $err;
        }

        $quizzes = $this->db->table('quizzes qz')
            ->select('qz.*, (SELECT COUNT(*) FROM quizzes_details d WHERE d.quizid = qz.id) AS total_questions', false)
            ->where('qz.classid', $cid)->orderBy('qz.id', 'DESC')
            ->get()->getResultArray();
        foreach ($quizzes as &$q) {
            $q['section'] = $class['section_name'];
            $q['schoolyear'] = $class['schoolyear_name'];
        }
        unset($q);

        return view('backend/pages/quiz/quizzes', [
            'pageTitle' => 'Class Quizzes',
            'scope' => $this->scope($class),
            'quizzes' => $quizzes,
            'quizTypes' => self::QUIZ_TYPES,
            'sections' => [],
            'schoolyears' => [],
        ]);
    }

    public function classQuizSave(int $cid)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }

        $p = $this->request->getJSON(true) ?? [];
        $term = (int) ($p['term'] ?? 0);
        $type = (string) ($p['quiz_type'] ?? '');
        if ($term < 1 || $term > 4) {
            return $this->fail('Select the term.');
        }
        if (!isset(self::QUIZ_TYPES[$type])) {
            return $this->fail('Select the quiz type.');
        }

        do {
            $code = 'QZ-' . strtoupper(bin2hex(random_bytes(3)));
        } while ($this->quizzes->where('quizcode', $code)->countAllResults() > 0);

        $id = $this->quizzes->insert([
            'quizcode' => $code,
            'classid' => $cid,
            'sectionid' => $class['sectionid'],
            'schoolyearid' => $class['schoolyearid'],
            'term' => $term,
            'quiz_type' => $type,
        ]);

        return $this->jsonOut([
            'status' => true,
            'message' => 'Quiz created. Now choose its questions.',
            'redirect' => route_to('get.class.quiz.builder', $cid, $id),
        ]);
    }

    public function classBuilder(int $cid, int $quizId)
    {
        [$class, $err] = $this->authorize($cid, false);
        if ($err) {
            return $err;
        }
        $quiz = $this->quizInClass($quizId, $cid);
        if (!$quiz) {
            return redirect()->to(route_to('get.class.quizzes', $cid));
        }
        $quiz['section'] = $class['section_name'];
        $quiz['schoolyear'] = $class['schoolyear_name'];

        $selected = $this->db->table('quizzes_details d')
            ->select('q.id, q.description, q.option_type')
            ->join('questions q', 'q.id = d.questionid')
            ->where('d.quizid', $quizId)->orderBy('d.id')
            ->get()->getResultArray();

        return view('backend/pages/quiz/builder', [
            'pageTitle' => 'Build Quiz',
            'scope' => $this->scope($class, ['sync' => route_to('post.class.quiz.sync', $cid, $quizId)]),
            'quiz' => $quiz,
            'selected' => $selected,
            'competencies' => $this->competencies($class['subjectid']),
            'optionTypes' => self::OPTION_TYPES,
            'taxonomy' => self::TAXONOMY,
            'quizTypes' => self::QUIZ_TYPES,
        ]);
    }

    public function classQuizSync(int $cid, int $quizId)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->quizInClass($quizId, $cid)) {
            return $this->fail('Quiz not found in this class.', 404);
        }

        return $this->syncQuiz($quizId, $class['subjectid']);
    }

    public function classQuizDelete(int $cid, int $quizId)
    {
        [, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->quizInClass($quizId, $cid)) {
            return $this->fail('Quiz not found in this class.', 404);
        }

        return $this->removeQuiz($quizId);
    }

    /* ==================== Live monitoring ==================== */

    private function monitorQuiz(int $cid, int $quizId): ?array
    {
        return $this->quizInClass($quizId, $cid);
    }

    private function attemptOf(int $quizId, int $attemptId): ?array
    {
        return $this->db->table('quiz_attempts')->where('id', $attemptId)->where('quizid', $quizId)->get()->getRowArray();
    }

    public function classMonitor(int $cid, int $quizId)
    {
        [$class, $err] = $this->authorize($cid, false);
        if ($err) {
            return $err;
        }
        $quiz = $this->monitorQuiz($cid, $quizId);
        if (!$quiz) {
            return redirect()->to(route_to('get.class.quizzes', $cid));
        }

        return view('backend/pages/quiz/monitor', [
            'pageTitle' => 'Monitor Quiz',
            'scope' => $this->scope($class),
            'quiz' => $quiz,
            'quizTypes' => self::QUIZ_TYPES,
            'urls' => [
                'data' => route_to('get.class.quiz.monitor.data', $cid, $quizId),
                'attempt' => $this->tpl('get.class.quiz.attempt', $cid, $quizId),
                'reset' => $this->tpl('post.class.quiz.attempt.reset', $cid, $quizId),
                'force' => $this->tpl('post.class.quiz.attempt.submit', $cid, $quizId),
                'grade' => $this->tpl('post.class.quiz.attempt.grade', $cid, $quizId),
            ],
        ]);
    }

    /** Polled by the monitor page every few seconds. */
    public function classMonitorData(int $cid, int $quizId)
    {
        [$class, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        $quiz = $this->monitorQuiz($cid, $quizId);
        if (!$quiz) {
            return $this->fail('Quiz not found in this class.', 404);
        }

        $students = $this->roster->students($class['sectionid']);
        $total = $this->db->table('quizzes_details')->where('quizid', $quizId)->countAllResults();

        $attempts = [];
        foreach ($this->db->table('quiz_attempts')->where('quizid', $quizId)->get()->getResultArray() as $a) {
            $a = $this->svc->expireIfNeeded($a);          // submits overdue attempts
            $attempts[$a['studentid']] = $a;
        }

        $agg = [];
        if ($attempts) {
            $rows = $this->db->table('quiz_answers')
                ->select('attemptid, COUNT(*) AS answered, SUM(is_correct = 1) AS correct, SUM(is_correct = 0) AS wrong, SUM(is_correct IS NULL) AS pending', false)
                ->whereIn('attemptid', array_column($attempts, 'id'))->groupBy('attemptid')->get()->getResultArray();
            foreach ($rows as $r) {
                $agg[$r['attemptid']] = $r;
            }
        }

        $now = time();
        $names = [];
        $out = [];
        $summary = ['enrolled' => count($students), 'not_started' => 0, 'in_progress' => 0, 'offline' => 0, 'submitted' => 0, 'flagged' => 0];

        foreach ($students as $st) {
            $names[$st['id']] = $st['name'];
            $a = $attempts[$st['id']] ?? null;

            if (!$a) {
                $summary['not_started']++;
                $out[] = ['studentid' => (int) $st['id'], 'name' => $st['name'], 'state' => 'not_started', 'attemptid' => null];
                continue;
            }

            $g = $agg[$a['id']] ?? ['answered' => 0, 'correct' => 0, 'wrong' => 0, 'pending' => 0];
            $seenAgo = $a['last_seen_at'] ? $now - strtotime($a['last_seen_at']) : null;
            $state = $a['status'];
            if ($state === 'in_progress') {
                $state = ($seenAgo !== null && $seenAgo <= 25) ? 'in_progress' : 'offline';
            }
            $summary[$state === 'auto_submitted' ? 'submitted' : $state]++;
            if ($a['status'] === 'auto_submitted' && $a['auto_reason'] === 'too_many_violations') {
                $summary['flagged']++;
            }

            $out[] = [
                'studentid' => (int) $st['id'],
                'name' => $st['name'],
                'attemptid' => (int) $a['id'],
                'state' => $state,
                'reason' => $a['auto_reason'],
                'answered' => (int) $g['answered'],
                'correct' => (int) $g['correct'],
                'wrong' => (int) $g['wrong'],
                'pending' => (int) $g['pending'],
                'score' => (float) $a['score'],
                'current' => (int) $a['current_index'] + 1,
                'violations' => (int) $a['violations'],
                'seen_ago' => $seenAgo,
            ];
        }

        $feed = $this->db->table('quiz_events e')
            ->select('e.event_type, e.detail, e.created_at, a.studentid')
            ->join('quiz_attempts a', 'a.id = e.attemptid')
            ->where('a.quizid', $quizId)->orderBy('e.id', 'DESC')->limit(40)
            ->get()->getResultArray();
        foreach ($feed as &$f) {
            $f['name'] = $names[$f['studentid']] ?? ('Student #' . $f['studentid']);
            $f['ago'] = $now - strtotime($f['created_at']);
        }
        unset($f);

        return $this->jsonOut([
            'status' => true,
            'total' => $total,
            'summary' => $summary,
            'rows' => $out,
            'feed' => $feed,
        ]);
    }

    /** One student's answers (in the order they got them) and event log. */
    public function classAttempt(int $cid, int $quizId, int $attemptId)
    {
        [, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        $a = $this->monitorQuiz($cid, $quizId) ? $this->attemptOf($quizId, $attemptId) : null;
        if (!$a) {
            return $this->fail('Attempt not found.', 404);
        }

        $order = json_decode($a['question_order'], true) ?: [];
        $qs = [];
        foreach ($this->db->table('questions')->whereIn('id', $order)->get()->getResultArray() as $q) {
            $qs[$q['id']] = $q;
        }
        $opts = [];
        foreach ($this->db->table('options')->whereIn('questionid', $order)->orderBy('id')->get()->getResultArray() as $o) {
            $opts[$o['questionid']][] = $o;
        }
        $ans = [];
        foreach ($this->db->table('quiz_answers')->where('attemptid', $attemptId)->get()->getResultArray() as $r) {
            $ans[$r['questionid']] = $r;
        }

        $rows = [];
        foreach ($order as $n => $qid) {
            if (!isset($qs[$qid])) {
                continue;
            }
            $q = $qs[$qid];
            $o = $opts[$qid] ?? [];
            $r = $ans[$qid] ?? null;
            $rows[] = [
                'no' => $n + 1,
                'answerid' => $r ? (int) $r['id'] : null,
                'question' => $q['description'],
                'type' => $q['option_type'],
                'type_label' => self::OPTION_TYPES[$q['option_type']] ?? $q['option_type'],
                'answered' => (bool) $r,
                'answer' => $r ? QuizGrader::answerText($q, $o, json_decode($r['answer'], true)) : null,
                'correct' => QuizGrader::correctText($q, $o),
                'is_correct' => $r ? ($r['is_correct'] === null ? null : (int) $r['is_correct']) : null,
                'score' => $r ? $r['score'] : null,
                'answered_at' => $r['answered_at'] ?? null,
            ];
        }

        $events = $this->db->table('quiz_events')->where('attemptid', $attemptId)->orderBy('id', 'DESC')->limit(150)->get()->getResultArray();

        return $this->jsonOut([
            'status' => true,
            'attempt' => [
                'status' => $a['status'],
                'reason' => $a['auto_reason'],
                'score' => (float) $a['score'],
                'violations' => (int) $a['violations'],
                'started_at' => $a['started_at'],
                'submitted' => $a['submitted_at'],
                'ip' => $a['ip'],
                'agent' => $a['user_agent'],
            ],
            'rows' => $rows,
            'events' => $events,
        ]);
    }

    /** Teacher resets a student's attempt so the student can start again. */
    public function classAttemptReset(int $cid, int $quizId, int $attemptId)
    {
        [, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->monitorQuiz($cid, $quizId) || !$this->attemptOf($quizId, $attemptId)) {
            return $this->fail('Attempt not found.', 404);
        }

        $this->db->transStart();
        $this->db->table('quiz_answers')->where('attemptid', $attemptId)->delete();
        $this->db->table('quiz_events')->where('attemptid', $attemptId)->delete();
        $this->db->table('quiz_attempts')->where('id', $attemptId)->delete();
        $this->db->transComplete();

        return $this->jsonOut(['status' => true, 'message' => 'Attempt reset. The student can start again.']);
    }

    public function classAttemptSubmit(int $cid, int $quizId, int $attemptId)
    {
        [, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->monitorQuiz($cid, $quizId) || !$this->attemptOf($quizId, $attemptId)) {
            return $this->fail('Attempt not found.', 404);
        }

        $this->svc->finalize($attemptId, 'auto_submitted', 'forced_by_teacher');

        return $this->jsonOut(['status' => true, 'message' => 'Attempt submitted.']);
    }

    /** Manual score (0 - 1) for an essay answer. */
    public function classAttemptGrade(int $cid, int $quizId, int $attemptId)
    {
        [, $err] = $this->authorize($cid, true);
        if ($err) {
            return $err;
        }
        if (!$this->monitorQuiz($cid, $quizId) || !$this->attemptOf($quizId, $attemptId)) {
            return $this->fail('Attempt not found.', 404);
        }

        $p = $this->request->getJSON(true) ?? [];
        $score = (float) ($p['score'] ?? -1);
        if ($score < 0 || $score > 1) {
            return $this->fail('Score must be between 0 and 1.');
        }

        $row = $this->db->table('quiz_answers qa')->select('qa.id, q.option_type')
            ->join('questions q', 'q.id = qa.questionid')
            ->where('qa.id', (int) ($p['answerid'] ?? 0))->where('qa.attemptid', $attemptId)->get()->getRowArray();
        if (!$row || $row['option_type'] !== 'ES') {
            return $this->fail('Only essay answers are graded manually.');
        }

        $this->db->table('quiz_answers')->where('id', $row['id'])->update([
            'score' => $score,
            'is_correct' => $score >= 1 ? 1 : 0,
            'graded_manually' => 1,
        ]);
        $total = $this->svc->recompute($attemptId);

        return $this->jsonOut(['status' => true, 'message' => 'Essay graded.', 'score' => $total]);
    }
}
