<?php

namespace App\Controllers\Api;

use App\Libraries\QuizAttemptService;
use App\Libraries\QuizGrader;
use App\Libraries\QuizRoster;

/**
 * Students taking quizzes.
 *
 * Server-side protections (the browser-side ones in take.php can only deter and record):
 *  - questions are sent ONE AT A TIME; correct answers never reach the browser while the quiz runs
 *  - every answer is graded on the server
 *  - question order is fixed per student (refreshing cannot re-roll it); options shuffled per student
 *  - the deadline is enforced by the server, late answers are rejected
 *  - one live session per attempt: opening the quiz in a second tab/device locks the first
 *  - warnings are counted on the server and the attempt is auto-submitted at the limit
 */
class StudentQuizController extends QuizController
{
    private QuizRoster $roster;
    private QuizAttemptService $svc;

    public function __construct()
    {
        parent::__construct();
        $this->roster = new QuizRoster();
        $this->svc = new QuizAttemptService();
    }

    /* ------------------------------------------------------------ helpers */

    private function isStudent(): bool
    {
        return $this->role() === 'student';
    }

    /** @return array{0: ?array, 1: mixed} [quiz, error response] */
    private function loadQuiz(int $quizId, bool $json): array
    {
        if (!$this->isStudent()) {
            return [null, $json ? $this->denyJson() : $this->denyPage()];
        }

        $quiz = $this->quizzes->find($quizId);
        if (!$quiz || empty($quiz['classid']) || !$this->roster->isEnrolled($this->userId(), (int) $quiz['sectionid'])) {
            return [null, $json ? $this->fail('Quiz not found.', 404) : redirect()->to(route_to('student.quizzes'))];
        }

        return [$quiz, null];
    }

    private function attemptRow(int $quizId): ?array
    {
        return $this->db->table('quiz_attempts')
            ->where('quizid', $quizId)->where('studentid', $this->userId())
            ->get()->getRowArray();
    }

    /** Validates quiz + attempt + session token for every in-quiz API call. @return array{0: ?array, 1: ?array, 2: mixed} */
    private function guard(int $quizId, ?string $token): array
    {
        [$quiz, $err] = $this->loadQuiz($quizId, true);
        if ($err) {
            return [null, null, $err];
        }

        $a = $this->attemptRow($quizId);
        if (!$a) {
            return [null, null, $this->fail('Start the quiz first.', 409)];
        }

        $a = $this->svc->expireIfNeeded($a);
        if ($a['status'] !== 'in_progress') {
            return [null, null, $this->jsonOut(['status' => false, 'code' => 'closed', 'message' => 'This quiz has ended.'], 410)];
        }
        if (!hash_equals((string) $a['session_token'], (string) $token)) {
            return [
                null,
                null,
                $this->jsonOut([
                    'status' => false,
                    'code' => 'replaced',
                    'message' => 'This quiz was opened in another tab or device, so this one is locked.'
                ], 409)
            ];
        }

        return [$quiz, $a, null];
    }

    private function remaining(array $a): ?int
    {
        return $a['deadline_at'] ? max(0, strtotime($a['deadline_at']) - time()) : null;
    }

    private function urls(int $quizId): array
    {
        $cut = static fn(string $u) => strstr($u, '9999999', true);

        return [
            'start' => route_to('student.quiz.start', $quizId),
            'question' => $cut(route_to('student.quiz.question', $quizId, 9999999)),
            'answer' => route_to('student.quiz.answer', $quizId),
            'event' => route_to('student.quiz.event', $quizId),
            'heartbeat' => route_to('student.quiz.heartbeat', $quizId),
            'submit' => route_to('student.quiz.submit', $quizId),
            'intro' => route_to('student.quiz.intro', $quizId),
        ];
    }

    private function subjectName(array $quiz): string
    {
        $row = $this->db->table(QuizRoster::CLASS_TABLE . ' c')
            ->select('s.name')
            ->join('subjects s', 's.id = c.' . QuizRoster::COL['subject'], 'left')
            ->where('c.id', $quiz['classid'])->get()->getRowArray();

        return (string) ($row['name'] ?? '');
    }

    private function shuffled(array $rows, string $seed, string $key): array
    {
        usort($rows, static fn($x, $y) => strcmp(md5($seed . '-' . $x[$key]), md5($seed . '-' . $y[$key])));

        return $rows;
    }

    /* ------------------------------------------------------------ pages */

    public function index()
    {
        if (!$this->isStudent()) {
            return $this->denyPage();
        }

        $sections = $this->roster->sectionIds($this->userId());
        $rows = [];
        if ($sections) {
            $rows = $this->db->table('quizzes qz')
                ->select('qz.id, qz.quizcode, qz.term, qz.quiz_type, qz.time_limit, qz.isactive, s.name AS subject,
                          a.status, a.score,
                          (SELECT COUNT(*) FROM quizzes_details d WHERE d.quizid = qz.id) AS total', false)
                ->join(QuizRoster::CLASS_TABLE . ' c', 'c.id = qz.classid')
                ->join('subjects s', 's.id = c.' . QuizRoster::COL['subject'], 'left')
                ->join('quiz_attempts a', 'a.quizid = qz.id AND a.studentid = ' . (int) $this->userId(), 'left')
                ->whereIn('qz.sectionid', $sections)
                ->groupStart()->where('qz.isactive', 'T')->orWhere('a.id IS NOT NULL', null, false)->groupEnd()
                ->orderBy('qz.id', 'DESC')->get()->getResultArray();
        }

        return view('backend/pages/quiz/student/quizzes', [
            'pageTitle' => 'My Quizzes',
            'quizzes' => $rows,
            'quizTypes' => self::QUIZ_TYPES,
        ]);
    }

    public function intro(int $quizId)
    {
        [$quiz, $err] = $this->loadQuiz($quizId, false);
        if ($err) {
            return $err;
        }

        $attempt = $this->attemptRow($quizId);
        if ($attempt) {
            $attempt = $this->svc->expireIfNeeded($attempt);
        }

        $total = $this->db->table('quizzes_details')->where('quizid', $quizId)->countAllResults();

        return view('backend/pages/quiz/student/intro', [
            'pageTitle' => 'Quiz',
            'quiz' => $quiz,
            'subject' => $this->subjectName($quiz),
            'quizTypes' => self::QUIZ_TYPES,
            'attempt' => $attempt,
            'total' => $total,
            'review' => ($attempt && $attempt['status'] !== 'in_progress' && $quiz['show_answer'] === 'T')
                ? $this->reviewRows($attempt) : [],
            'takeUrl' => route_to('student.quiz.take', $quizId),
            'listUrl' => route_to('student.quizzes'),
        ]);
    }

    public function take(int $quizId)
    {
        [$quiz, $err] = $this->loadQuiz($quizId, false);
        if ($err) {
            return $err;
        }

        $attempt = $this->attemptRow($quizId);
        if ($attempt) {
            $attempt = $this->svc->expireIfNeeded($attempt);
        }
        if (($attempt && $attempt['status'] !== 'in_progress') || (!$attempt && $quiz['isactive'] !== 'T')) {
            return redirect()->to(route_to('student.quiz.intro', $quizId));
        }

        return view('backend/pages/quiz/student/take', [
            'pageTitle' => 'Quiz',
            'quiz' => $quiz,
            'subject' => $this->subjectName($quiz),
            'total' => $this->db->table('quizzes_details')->where('quizid', $quizId)->countAllResults(),
            'urls' => $this->urls($quizId),
        ]);
    }

    /** Result review, only when the teacher enabled "show answers". */
    private function reviewRows(array $attempt): array
    {
        $order = json_decode($attempt['question_order'], true) ?: [];
        if (!$order) {
            return [];
        }

        $qs = [];
        foreach ($this->db->table('questions')->whereIn('id', $order)->get()->getResultArray() as $q) {
            $qs[$q['id']] = $q;
        }
        $opts = [];
        foreach ($this->db->table('options')->whereIn('questionid', $order)->orderBy('id')->get()->getResultArray() as $o) {
            $opts[$o['questionid']][] = $o;
        }
        $ans = [];
        foreach ($this->db->table('quiz_answers')->where('attemptid', $attempt['id'])->get()->getResultArray() as $r) {
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
                'question' => $q['description'],
                'yours' => $r ? QuizGrader::answerText($q, $o, json_decode($r['answer'], true)) : '(no answer)',
                'correct' => QuizGrader::correctText($q, $o),
                'score' => $r['score'] ?? null,
            ];
        }

        return $rows;
    }

    /* ------------------------------------------------------------ quiz API */

    public function start(int $quizId)
    {
        [$quiz, $err] = $this->loadQuiz($quizId, true);
        if ($err) {
            return $err;
        }

        $a = $this->attemptRow($quizId);
        $token = bin2hex(random_bytes(16));

        if ($a) {
            $a = $this->svc->expireIfNeeded($a);
            if ($a['status'] !== 'in_progress') {
                return $this->jsonOut(['status' => false, 'code' => 'closed', 'message' => 'You have already completed this quiz.'], 409);
            }

            $ago = $a['last_seen_at'] ? time() - strtotime($a['last_seen_at']) : null;
            $this->svc->log((int) $a['id'], 'resumed', $ago !== null ? "previous activity {$ago}s ago" : null);
            $this->db->table('quiz_attempts')->where('id', $a['id'])->update(['session_token' => $token, 'last_seen_at' => $this->svc->now()]);
        } else {
            if ($quiz['isactive'] !== 'T') {
                return $this->fail('This quiz is not open.', 403);
            }

            $ids = array_map('intval', array_column(
                $this->db->table('quizzes_details')->where('quizid', $quizId)->orderBy('id')->get()->getResultArray(),
                'questionid'
            ));
            if (!$ids) {
                return $this->fail('This quiz has no questions yet.');
            }
            if ($quiz['shuffle_questions'] === 'T') {
                shuffle($ids);
            }

            $now = $this->svc->now();
            $deadline = $quiz['time_limit'] ? date('Y-m-d H:i:s', time() + ((int) $quiz['time_limit']) * 60) : null;

            $this->db->table('quiz_attempts')->insert([
                'quizid' => $quizId,
                'studentid' => $this->userId(),
                'status' => 'in_progress',
                'question_order' => json_encode($ids),
                'option_seed' => random_int(1, 2000000000),
                'session_token' => $token,
                'started_at' => $now,
                'deadline_at' => $deadline,
                'last_seen_at' => $now,
                'ip' => $this->request->getIPAddress(),
                'user_agent' => mb_substr((string) $this->request->getUserAgent(), 0, 255),
            ]);
            $a = $this->attemptRow($quizId);
            $this->svc->log((int) $a['id'], 'started');
        }

        $order = json_decode($a['question_order'], true);
        $answered = [];
        foreach ($this->db->table('quiz_answers')->select('questionid')->where('attemptid', $a['id'])->get()->getResultArray() as $r) {
            $idx = array_search((int) $r['questionid'], array_map('intval', $order), true);
            if ($idx !== false) {
                $answered[] = $idx;
            }
        }

        return $this->jsonOut([
            'status' => true,
            'session_token' => $token,
            'total' => count($order),
            'index' => (int) $a['current_index'],
            'remaining' => $this->remaining($a),
            'violations' => (int) $a['violations'],
            'max' => (int) $quiz['max_violations'],
            'answered' => $answered,
        ]);
    }

    public function question(int $quizId, int $i)
    {
        [$quiz, $a, $err] = $this->guard($quizId, $this->request->getGet('st'));
        if ($err) {
            return $err;
        }

        $order = json_decode($a['question_order'], true);
        if ($i < 0 || $i >= count($order)) {
            return $this->fail('Question not found.', 404);
        }

        $qid = (int) $order[$i];
        $q = $this->questions->find($qid);
        $opts = $this->options->where('questionid', $qid)->orderBy('id')->findAll();
        $seed = $a['option_seed'] . '-' . $qid;

        $out = [
            'index' => $i,
            'total' => count($order),
            'type' => $q['option_type'],
            'text' => $q['description'],
        ];

        switch ($q['option_type']) {
            case 'MC':
            case 'TF':
                $list = ($q['option_type'] === 'MC' && $quiz['shuffle_options'] === 'T') ? $this->shuffled($opts, $seed, 'id') : $opts;
                $out['options'] = array_map(static fn($o) => ['id' => (int) $o['id'], 'text' => $o['description']], $list);
                break;
            case 'FB':
                $out['blanks'] = QuizGrader::blanks($q['description']);
                break;
            case 'MT':
                $items = $quiz['shuffle_options'] === 'T' ? $this->shuffled($opts, $seed, 'id') : $opts;
                $out['items'] = array_map(static fn($o) => ['id' => (int) $o['id'], 'text' => $o['description']], $items);
                $choices = array_map(static fn($t) => ['t' => $t], array_values(array_unique(array_column($opts, 'match_text'))));
                $out['choices'] = array_column($this->shuffled($choices, $seed, 't'), 't');
                break;
        }

        $saved = $this->db->table('quiz_answers')->where('attemptid', $a['id'])->where('questionid', $qid)->get()->getRowArray();
        $out['saved'] = $saved ? json_decode($saved['answer'], true) : null;

        if ((int) $a['current_index'] !== $i) {
            $this->svc->log((int) $a['id'], 'question_viewed', 'Q' . ($i + 1));
        }
        $this->db->table('quiz_attempts')->where('id', $a['id'])->update(['current_index' => $i, 'last_seen_at' => $this->svc->now()]);

        return $this->jsonOut(['status' => true, 'question' => $out]);
    }

    public function answer(int $quizId)
    {
        $p = $this->request->getJSON(true) ?? [];
        [$quiz, $a, $err] = $this->guard($quizId, $p['session_token'] ?? null);
        if ($err) {
            return $err;
        }

        $order = json_decode($a['question_order'], true);
        $i = (int) ($p['index'] ?? -1);
        if ($i < 0 || $i >= count($order)) {
            return $this->fail('Question not found.', 404);
        }

        $qid = (int) $order[$i];
        $q = $this->questions->find($qid);
        $opts = $this->options->where('questionid', $qid)->orderBy('id')->findAll();
        $raw = $p['answer'] ?? null;

        // normalise the answer to the shape the question type expects
        switch ($q['option_type']) {
            case 'MC':
            case 'TF':
                $ans = (int) $raw;
                if (!in_array($ans, array_map('intval', array_column($opts, 'id')), true)) {
                    return $this->fail('Invalid choice.');
                }
                break;
            case 'ID':
                $ans = mb_substr(trim((string) $raw), 0, 255);
                break;
            case 'ES':
                $ans = mb_substr(trim((string) $raw), 0, 8000);
                break;
            case 'FB':
                $n = QuizGrader::blanks($q['description']);
                $ans = [];
                for ($k = 0; $k < $n; $k++) {
                    $ans[] = mb_substr(trim((string) (is_array($raw) ? ($raw[$k] ?? '') : '')), 0, 255);
                }
                break;
            case 'MT':
                $ans = [];
                foreach ($opts as $o) {
                    $ans[$o['id']] = mb_substr(trim((string) (is_array($raw) ? ($raw[$o['id']] ?? '') : '')), 0, 255);
                }
                break;
            default:
                return $this->fail('Unsupported question.');
        }

        [$score, $correct] = QuizGrader::grade($q, $opts, $ans);
        $existing = $this->db->table('quiz_answers')->where('attemptid', $a['id'])->where('questionid', $qid)->get()->getRowArray();

        $data = ['answer' => json_encode($ans), 'answered_at' => $this->svc->now()];
        if (!($existing && $existing['graded_manually'])) {     // never overwrite the teacher's essay grade
            $data['score'] = $score;
            $data['is_correct'] = $correct;
        }

        if ($existing) {
            $this->db->table('quiz_answers')->where('id', $existing['id'])->update($data);
            if ($existing['answer'] !== $data['answer'] && $q['option_type'] !== 'ES') {
                $this->svc->log((int) $a['id'], 'answer_changed', 'Q' . ($i + 1));
            }
        } else {
            $this->db->table('quiz_answers')->insert($data + ['attemptid' => $a['id'], 'questionid' => $qid]);
            $this->svc->log((int) $a['id'], 'answered', 'Q' . ($i + 1));
        }

        $this->db->table('quiz_attempts')->where('id', $a['id'])->update(['current_index' => $i, 'last_seen_at' => $this->svc->now()]);

        return $this->jsonOut(['status' => true, 'answered' => $this->db->table('quiz_answers')->where('attemptid', $a['id'])->countAllResults()]);
    }

    public function event(int $quizId)
    {
        $p = $this->request->getJSON(true) ?? [];
        [$quiz, $a, $err] = $this->guard($quizId, $p['session_token'] ?? null);
        if ($err) {
            return $err;
        }

        $type = (string) ($p['event_type'] ?? '');
        if (!in_array($type, QuizAttemptService::CLIENT_EVENTS, true)) {
            return $this->fail('Unknown event.');
        }

        [$violations, $counted, $terminated] = $this->svc->registerEvent($a, $type, $p['detail'] ?? null, (int) $quiz['max_violations']);

        return $this->jsonOut([
            'status' => true,
            'violations' => $violations,
            'counted' => $counted,
            'terminated' => $terminated,
            'max' => (int) $quiz['max_violations'],
        ]);
    }

    public function heartbeat(int $quizId)
    {
        $p = $this->request->getJSON(true) ?? [];
        [$quiz, $a, $err] = $this->guard($quizId, $p['session_token'] ?? null);
        if ($err) {
            return $err;
        }

        $this->db->table('quiz_attempts')->where('id', $a['id'])->update([
            'last_seen_at' => $this->svc->now(),
            'current_index' => max(0, (int) ($p['index'] ?? $a['current_index'])),
        ]);

        return $this->jsonOut(['status' => true, 'remaining' => $this->remaining($a), 'violations' => (int) $a['violations']]);
    }

    public function submit(int $quizId)
    {
        $p = $this->request->getJSON(true) ?? [];
        [$quiz, $a, $err] = $this->guard($quizId, $p['session_token'] ?? null);
        if ($err) {
            return $err;
        }

        $this->svc->finalize((int) $a['id'], 'submitted');
        $row = $this->attemptRow($quizId);
        $total = count(json_decode($a['question_order'], true));
        $show = $quiz['show_answer'] === 'T';

        return $this->jsonOut([
            'status' => true,
            'done' => true,
            'show' => $show,
            'score' => $show ? (float) $row['score'] : null,
            'total' => $total,
        ]);
    }
}
