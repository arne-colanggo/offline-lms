<?php
namespace App\Controllers\Api;

/**
 * Questions + quizzes managed from a Class page.
 * A class = a subject offered in a section. The teacher assigned to the class (or an admin)
 * can: write questions for that subject's BOW competencies, and build quizzes for that class.
 * Everything is checked against the class on every request.
 */
class ClassQuizController extends QuizController
{
    // >>> Adjust to your classes table <<<
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    private const CLASS_TABLE = 'classes';
    private const COL = [
        'subject' => 'subjectid',     // classes.<column> holding subjects.id
        'section' => 'sectionid',     // classes.<column> holding sections.id
        'schoolyear' => 'schoolyearid',  // classes.<column> holding schoolyears.id
        'teacher' => 'teacherid',     // classes.<column> holding users.id of the assigned teacher
    ];
    private const CLASS_PAGE = 'admin/view-class-details/?id=%d';   // your class details page URL, e.g. 'admin/class/?id=%d' (sprintf with the class id)

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
        ];
    }

    private const SENTINEL = '9999999';

    /** URL of an id route up to the id, e.g. ".../question/" (the JS appends the id). */
    private function prefix(string $route, int $cid): string
    {
        return strstr(route_to($route, $cid, (int) self::SENTINEL), self::SENTINEL, true);
    }

    /** URL of an id route with a {id} placeholder. */
    private function tpl(string $route, int $cid): string
    {
        return str_replace(self::SENTINEL, '{id}', route_to($route, $cid, (int) self::SENTINEL));
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
}