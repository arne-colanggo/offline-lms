<?php

// Same namespace as your other Api controllers (AdminController, ClassController, ...).
// Adjust the namespace / BaseController import if yours differ.
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\OptionModel;
use App\Models\QuestionModel;
use App\Models\QuizDetailModel;
use App\Models\QuizModel;

class QuizController extends BaseController
{
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public const OPTION_TYPES = [
        'MC' => 'Multiple Choice',
        'TF' => 'True or False',
        'ID' => 'Identification',
        'MT' => 'Matching Type',
        'FB' => 'Fill in the Blanks',
        'ES' => 'Essay',
    ];

    public const TAXONOMY = [
        'RM' => 'Remembering',
        'UN' => 'Understanding',
        'AP' => 'Applying',
        'AN' => 'Analyzing',
        'EV' => 'Evaluating',
        'CR' => 'Creating',
    ];

    public const QUIZ_TYPES = [
        'WW' => 'Written Work',
        'ST1' => 'Summative Test 1',
        'ST2' => 'Summative Test 2',
        'TE' => 'Term Exam',
    ];

    protected $db;
    protected QuestionModel $questions;
    protected OptionModel $options;
    protected QuizModel $quizzes;
    protected QuizDetailModel $details;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->questions = new QuestionModel();
        $this->options = new OptionModel();
        $this->quizzes = new QuizModel();
        $this->details = new QuizDetailModel();
    }

    /* ------------------------------------------------------------------
     * Access: the 'auth' filter only checks that someone is logged in, so
     * students could otherwise open the question bank (with the answers).
     * These admin-wide pages are admin only; teachers use ClassQuizController.
     * ------------------------------------------------------------------ */
    protected function userId(): int
    {
        return (int) (session()->get('userdata')['id'] ?? 0);
    }

    protected function role(): string
    {
        return (string) (session()->get('userdata')['role'] ?? '');
    }

    /** The admin-wide Question Bank / Quizzes pages are admin only; teachers work from their Class page. */
    protected function isAdmin(): bool
    {
        return $this->role() === 'admin';
    }

    /** Admins can change any question; teachers only the ones they created. */
    protected function ownsQuestion(array $q): bool
    {
        return $this->isAdmin() || ((int) ($q['createdby'] ?? 0) !== 0 && (int) $q['createdby'] === $this->userId());
    }

    protected function denyPage()
    {
        return redirect()->to(route_to('unauthorized'));
    }

    protected function denyJson()
    {
        return $this->jsonOut(['status' => false, 'message' => 'Not allowed.'], 403);
    }

    protected function fail(string $message, int $code = 422)
    {
        return $this->jsonOut(['status' => false, 'message' => $message], $code);
    }

    /** JSON response that also hands back the refreshed CSRF hash (your CSRF token regenerates on every POST). */
    protected function jsonOut(array $payload, int $code = 200)
    {
        $payload['token'] = csrf_hash();

        return $this->response->setStatusCode($code)->setJSON($payload);
    }

    /** Competencies (bow_details) with their BOW + subject, for the dropdowns. */
    protected function competencies(?int $subjectId = null): array
    {
        $b = $this->db->table('bow_details bd')
            ->select('bd.id, bd.competencycode, bd.learning_objectives, bd.week, b.name AS bow_name, b.term, s.name AS subject')
            ->join('bows b', 'b.id = bd.bowid')
            ->join('subjects s', 's.id = b.subjectid', 'left');
        if ($subjectId) {
            $b->where('b.subjectid', $subjectId);
        }

        return $b->orderBy('s.name')->orderBy('b.term')->orderBy('bd.week')->orderBy('bd.competencycode')
            ->get()->getResultArray();
    }

    /* ==================================================================
     * QUESTION BANK
     * ================================================================== */

    public function questions()
    {
        if (!$this->isAdmin()) {
            return $this->denyPage();
        }

        return view('backend/pages/quiz/questions', [
            'pageTitle' => 'Question Bank',
            'competencies' => $this->competencies(),
            'optionTypes' => self::OPTION_TYPES,
            'taxonomy' => self::TAXONOMY,
        ]);
    }

    /** JSON for the DataTables (question bank + quiz builder). */
    public function questionList()
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }

        return $this->jsonOut(['data' => $this->questionRows()]);
    }

    /** Question rows for the tables; $subjectId limits them to one subject's competencies. */
    protected function questionRows(?int $subjectId = null): array
    {
        $b = $this->db->table('questions q')
            ->select('q.id, q.createdby, q.bowdetailsid, q.option_type, q.question_type, q.description,
                      bd.competencycode, bd.learning_objectives, s.name AS subject,
                      (SELECT COUNT(*) FROM quizzes_details d WHERE d.questionid = q.id) AS used_in', false)
            ->join('bow_details bd', 'bd.id = q.bowdetailsid', 'left')
            ->join('bows b', 'b.id = bd.bowid', 'left')
            ->join('subjects s', 's.id = b.subjectid', 'left')
            ->orderBy('q.id', 'DESC');

        if ($subjectId) {
            $b->where('b.subjectid', $subjectId);
        }
        if ($v = (int) $this->request->getGet('bowdetailsid')) {
            $b->where('q.bowdetailsid', $v);
        }
        $ot = $this->request->getGet('option_type');
        if ($ot && isset(self::OPTION_TYPES[$ot])) {
            $b->where('q.option_type', $ot);
        }
        $qt = $this->request->getGet('question_type');
        if ($qt && isset(self::TAXONOMY[$qt])) {
            $b->where('q.question_type', $qt);
        }

        $rows = $b->get()->getResultArray();
        foreach ($rows as &$r) {
            $r['option_label'] = self::OPTION_TYPES[$r['option_type']] ?? $r['option_type'];
            $r['taxonomy_label'] = self::TAXONOMY[$r['question_type']] ?? $r['question_type'];
            $r['mine'] = $this->ownsQuestion($r);
        }
        unset($r);

        return $rows;
    }

    public function questionGet(int $id)
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }

        $q = $this->questions->find($id);
        if (!$q) {
            return $this->fail('Question not found.', 404);
        }
        $q['options'] = $this->options->where('questionid', $id)->orderBy('id')->findAll();

        return $this->jsonOut(['status' => true, 'data' => $q]);
    }

    public function questionSave()
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }

        return $this->storeQuestion($this->request->getJSON(true) ?? []);
    }

    /** $competencyIds, when given, limits the competency to those ids (class-scoped save). */
    protected function storeQuestion(array $p, ?array $competencyIds = null)
    {
        $id = (int) ($p['id'] ?? 0);
        $bdid = (int) ($p['bowdetailsid'] ?? 0);
        $ot = (string) ($p['option_type'] ?? '');
        $qt = (string) ($p['question_type'] ?? '');
        $desc = trim((string) ($p['description'] ?? ''));

        if ($this->db->table('bow_details')->where('id', $bdid)->countAllResults() === 0) {
            return $this->fail('Select the competency this question is based on.');
        }
        if ($competencyIds !== null && !in_array($bdid, $competencyIds, true)) {
            return $this->fail('That competency does not belong to this class subject.');
        }
        if (!isset(self::OPTION_TYPES[$ot])) {
            return $this->fail('Select a valid question type.');
        }
        if (!isset(self::TAXONOMY[$qt])) {
            return $this->fail('Select a valid taxonomy level.');
        }
        if ($desc === '') {
            return $this->fail('Enter the question.');
        }

        [$clean, $error] = $this->cleanOptions($ot, $desc, $p['options'] ?? []);
        if ($error) {
            return $this->fail($error);
        }

        $data = [
            'bowdetailsid' => $bdid,
            'option_type' => $ot,
            'question_type' => $qt,
            'description' => $desc,
        ];

        if ($id) {
            $existing = $this->questions->find($id);
            if (!$existing) {
                return $this->fail('Question not found.', 404);
            }
            if (!$this->ownsQuestion($existing)) {
                return $this->fail('You can only edit questions you created.', 403);
            }
        }

        $this->db->transStart();
        if ($id) {
            $this->questions->update($id, $data);
            $this->db->table('options')->where('questionid', $id)->delete();
        } else {
            $data['createdby'] = $this->userId() ?: null;
            $id = (int) $this->questions->insert($data);
        }
        foreach ($clean as &$c) {
            $c['questionid'] = $id;
        }
        unset($c);
        if ($clean) {
            $this->options->insertBatch($clean);
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->fail('Could not save the question.', 500);
        }

        return $this->jsonOut(['status' => true, 'message' => 'Question saved.', 'id' => $id]);
    }

    /**
     * Validates / normalises the options for the chosen question type.
     * Returns [rows, errorMessage].
     *
     *  MC - 2+ choices, exactly one correct
     *  TF - fixed True/False, one correct
     *  ID - 1+ accepted answers (all stored as answers)
     *  FB - one answer per blank (___) in the question, in order
     *  MT - 2+ pairs: description = item, match_text = its match
     *  ES - no options
     */
    protected function cleanOptions(string $type, string $questionText, array $raw): array
    {
        $rows = [];
        foreach ($raw as $o) {
            $rows[] = [
                'description' => trim((string) ($o['description'] ?? '')),
                'match_text' => trim((string) ($o['match_text'] ?? '')),
                'correct' => !empty($o['isanswer']),
            ];
        }

        switch ($type) {
            case 'ES':
                return [[], null];

            case 'TF':
                $trueIsCorrect = false;
                $falseIsCorrect = false;
                foreach ($rows as $r) {
                    if (strcasecmp($r['description'], 'True') === 0 && $r['correct']) {
                        $trueIsCorrect = true;
                    }
                    if (strcasecmp($r['description'], 'False') === 0 && $r['correct']) {
                        $falseIsCorrect = true;
                    }
                }
                if ($trueIsCorrect === $falseIsCorrect) {
                    return [[], 'Mark either True or False as the correct answer.'];
                }
                return [
                    [
                        ['description' => 'True', 'match_text' => null, 'isanswer' => $trueIsCorrect ? 'T' : 'F'],
                        ['description' => 'False', 'match_text' => null, 'isanswer' => $falseIsCorrect ? 'T' : 'F'],
                    ],
                    null
                ];

            case 'MC':
                $rows = array_values(array_filter($rows, static fn($r) => $r['description'] !== ''));
                if (count($rows) < 2) {
                    return [[], 'Multiple choice needs at least two options.'];
                }
                if (count(array_unique(array_map('mb_strtolower', array_column($rows, 'description')))) !== count($rows)) {
                    return [[], 'Two options have the same text.'];
                }
                if (count(array_filter($rows, static fn($r) => $r['correct'])) !== 1) {
                    return [[], 'Mark exactly one option as the correct answer.'];
                }
                return [
                    array_map(static fn($r) => [
                        'description' => $r['description'],
                        'match_text' => null,
                        'isanswer' => $r['correct'] ? 'T' : 'F',
                    ], $rows),
                    null
                ];

            case 'ID':
            case 'FB':
                $rows = array_values(array_filter($rows, static fn($r) => $r['description'] !== ''));
                if (!$rows) {
                    return [[], $type === 'ID' ? 'Enter at least one accepted answer.' : 'Enter the answer for each blank.'];
                }
                if ($type === 'FB') {
                    $blanks = preg_match_all('/_{3,}/', $questionText);
                    if ($blanks === 0) {
                        return [[], 'Mark each blank in the question with three or more underscores (___).'];
                    }
                    if ($blanks !== count($rows)) {
                        return [[], "The question has {$blanks} blank(s) but you entered " . count($rows) . ' answer(s).'];
                    }
                }
                return [
                    array_map(static fn($r) => [
                        'description' => $r['description'],
                        'match_text' => null,
                        'isanswer' => 'T',
                    ], $rows),
                    null
                ];

            case 'MT':
                $rows = array_values(array_filter($rows, static fn($r) => $r['description'] !== '' || $r['match_text'] !== ''));
                foreach ($rows as $r) {
                    if ($r['description'] === '' || $r['match_text'] === '') {
                        return [[], 'Every matching pair needs both an item and its match.'];
                    }
                }
                if (count($rows) < 2) {
                    return [[], 'Matching type needs at least two pairs.'];
                }
                return [
                    array_map(static fn($r) => [
                        'description' => $r['description'],
                        'match_text' => $r['match_text'],
                        'isanswer' => 'T',
                    ], $rows),
                    null
                ];
        }

        return [[], 'Unknown question type.'];
    }

    public function questionDelete(int $id)
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }

        return $this->removeQuestion($id);
    }

    protected function removeQuestion(int $id)
    {
        $q = $this->questions->find($id);
        if (!$q) {
            return $this->fail('Question not found.', 404);
        }
        if (!$this->ownsQuestion($q)) {
            return $this->fail('You can only delete questions you created.', 403);
        }

        $used = $this->details->where('questionid', $id)->countAllResults();
        if ($used > 0) {
            return $this->fail("This question is used in {$used} quiz(zes). Remove it from them first.", 409);
        }

        $this->db->transStart();
        $this->db->table('options')->where('questionid', $id)->delete();
        $this->questions->delete($id);
        $this->db->transComplete();

        return $this->jsonOut(['status' => true, 'message' => 'Question deleted.']);
    }

    /* ==================================================================
     * QUIZZES
     * ================================================================== */

    public function quizzes()
    {
        if (!$this->isAdmin()) {
            return $this->denyPage();
        }

        $quizzes = $this->db->table('quizzes qz')
            ->select('qz.*, sc.name AS section, sy.name AS schoolyear,
                      (SELECT COUNT(*) FROM quizzes_details d WHERE d.quizid = qz.id) AS total_questions', false)
            ->join('sections sc', 'sc.id = qz.sectionid', 'left')
            ->join('schoolyears sy', 'sy.id = qz.schoolyearid', 'left')
            ->orderBy('qz.id', 'DESC')
            ->get()->getResultArray();

        return view('backend/pages/quiz/quizzes', [
            'pageTitle' => 'Quizzes',
            'quizzes' => $quizzes,
            'quizTypes' => self::QUIZ_TYPES,
            'sections' => $this->db->table('sections')->orderBy('grade_level_id')->orderBy('ordering')->get()->getResultArray(),
            'schoolyears' => $this->db->table('schoolyears')->orderBy('id', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function quizSave()
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }

        $p = $this->request->getJSON(true) ?? [];
        $sectionId = (int) ($p['sectionid'] ?? 0);
        $syId = (int) ($p['schoolyearid'] ?? 0);
        $term = (int) ($p['term'] ?? 0);
        $type = (string) ($p['quiz_type'] ?? '');

        if ($this->db->table('sections')->where('id', $sectionId)->countAllResults() === 0) {
            return $this->fail('Select a section.');
        }
        if ($this->db->table('schoolyears')->where('id', $syId)->countAllResults() === 0) {
            return $this->fail('Select a school year.');
        }
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
            'sectionid' => $sectionId,
            'schoolyearid' => $syId,
            'term' => $term,
            'quiz_type' => $type,
        ]);

        return $this->jsonOut([
            'status' => true,
            'message' => 'Quiz created. Now choose its questions.',
            'redirect' => route_to('get.quiz.builder', $id),
        ]);
    }

    public function builder(int $id)
    {
        if (!$this->isAdmin()) {
            return $this->denyPage();
        }

        $quiz = $this->db->table('quizzes qz')
            ->select('qz.*, sc.name AS section, sy.name AS schoolyear')
            ->join('sections sc', 'sc.id = qz.sectionid', 'left')
            ->join('schoolyears sy', 'sy.id = qz.schoolyearid', 'left')
            ->where('qz.id', $id)->get()->getRowArray();
        if (!$quiz) {
            return redirect()->to(route_to('get.quizzes'));
        }

        $selected = $this->db->table('quizzes_details d')
            ->select('q.id, q.description, q.option_type')
            ->join('questions q', 'q.id = d.questionid')
            ->where('d.quizid', $id)->orderBy('d.id')
            ->get()->getResultArray();

        return view('backend/pages/quiz/builder', [
            'pageTitle' => 'Build Quiz',
            'quiz' => $quiz,
            'selected' => $selected,
            'competencies' => $this->competencies(),
            'optionTypes' => self::OPTION_TYPES,
            'taxonomy' => self::TAXONOMY,
            'quizTypes' => self::QUIZ_TYPES,
        ]);
    }

    /** Saves quiz settings + the full list of selected question ids. */
    public function quizSync(int $id)
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }

        return $this->syncQuiz($id);
    }

    /** $subjectId, when given, requires every selected question to belong to that subject. */
    protected function syncQuiz(int $id, ?int $subjectId = null)
    {
        if (!$this->quizzes->find($id)) {
            return $this->fail('Quiz not found.', 404);
        }

        $p = $this->request->getJSON(true) ?? [];
        $type = (string) ($p['quiz_type'] ?? '');
        $term = (int) ($p['term'] ?? 0);
        if (!isset(self::QUIZ_TYPES[$type]) || $term < 1 || $term > 4) {
            return $this->fail('Choose a valid quiz type and term.');
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $p['question_ids'] ?? []))));
        if ($ids) {
            $found = $this->db->table('questions')->whereIn('id', $ids)->countAllResults();
            if ($found !== count($ids)) {
                return $this->fail('Some selected questions no longer exist. Reload the page.');
            }
            if ($subjectId) {
                $inSubject = $this->db->table('questions q')
                    ->join('bow_details bd', 'bd.id = q.bowdetailsid')
                    ->join('bows b', 'b.id = bd.bowid')
                    ->whereIn('q.id', $ids)->where('b.subjectid', $subjectId)->countAllResults();
                if ($inSubject !== count($ids)) {
                    return $this->fail('Some selected questions are not from this class subject.');
                }
            }
        }

        $flag = static fn($k) => !empty($p[$k]) ? 'T' : 'F';

        $timeLimit = (($p['time_limit'] ?? '') === '' || $p['time_limit'] === null) ? null : max(1, (int) $p['time_limit']);
        $maxViolations = max(0, min(20, (int) ($p['max_violations'] ?? 3)));

        // Once students have started, the question set is frozen (their order/answers depend on it)
        if ($this->db->table('quiz_attempts')->where('quizid', $id)->countAllResults() > 0) {
            $current = array_map('intval', array_column($this->db->table('quizzes_details')->where('quizid', $id)->get()->getResultArray(), 'questionid'));
            $new = array_map('intval', $ids);
            sort($current);
            sort($new);
            if ($current !== $new) {
                return $this->fail('Students have already started this quiz. Reset their attempts before changing its questions.', 409);
            }
        }

        $this->db->transStart();
        $this->quizzes->update($id, [
            'quiz_type' => $type,
            'term' => $term,
            'show_answer' => $flag('show_answer'),
            'isactive' => $flag('isactive'),
            'shuffle_questions' => $flag('shuffle_questions'),
            'shuffle_options' => $flag('shuffle_options'),
            'time_limit' => $timeLimit,
            'max_violations' => $maxViolations,
        ]);
        $this->db->table('quizzes_details')->where('quizid', $id)->delete();
        if ($ids) {
            $this->details->insertBatch(array_map(static fn($qid) => ['quizid' => $id, 'questionid' => $qid], $ids));
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->fail('Could not save the quiz.', 500);
        }

        return $this->jsonOut(['status' => true, 'message' => 'Quiz saved (' . count($ids) . ' questions).']);
    }

    public function quizDelete(int $id)
    {
        if (!$this->isAdmin()) {
            return $this->denyJson();
        }
        //return $this->jsonOut(['status' => true, 'message' => 'Test Message']);
        return $this->removeQuiz($id);
    }

    protected function removeQuiz(int $id)
    {
        if (!$this->quizzes->find($id)) {
            return $this->fail('Quiz not found.', 404);
        }

        $this->db->transStart();
        $attemptIds = array_column($this->db->table('quiz_attempts')->select('id')->where('quizid', $id)->get()->getResultArray(), 'id');
        if ($attemptIds) {
            $this->db->table('quiz_answers')->whereIn('attemptid', $attemptIds)->delete();
            $this->db->table('quiz_events')->whereIn('attemptid', $attemptIds)->delete();
            $this->db->table('quiz_attempts')->whereIn('id', $attemptIds)->delete();
        }
        $this->db->table('quizzes_details')->where('quizid', $id)->delete();
        $this->quizzes->delete($id);
        $this->db->transComplete();

        return $this->jsonOut(['status' => true, 'message' => 'Quiz deleted.']);
    }
}