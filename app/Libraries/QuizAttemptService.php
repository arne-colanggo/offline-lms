<?php

namespace App\Libraries;

/** Attempt lifecycle + anti-cheat bookkeeping shared by the student and teacher controllers. */
class QuizAttemptService
{
    /** Events the browser may report. */
    public const CLIENT_EVENTS = [
        'tab_hidden', 'window_blur', 'fullscreen_exit', 'copy', 'cut', 'paste',
        'contextmenu', 'shortcut_blocked', 'fullscreen_denied',
    ];

    /** Events that count as a warning. The rest are only logged. */
    public const VIOLATION_EVENTS = ['tab_hidden', 'window_blur', 'fullscreen_exit', 'copy', 'cut', 'paste'];

    /** A tab switch fires several events at once (hidden + blur); count them as one. */
    private const VIOLATION_GAP_SECONDS = 2;

    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function log(int $attemptId, string $type, ?string $detail = null): void
    {
        $this->db->table('quiz_events')->insert([
            'attemptid'  => $attemptId,
            'event_type' => $type,
            'detail'     => $detail !== null ? mb_substr($detail, 0, 255) : null,
            'created_at' => $this->now(),
        ]);
    }

    public function recompute(int $attemptId): float
    {
        $row   = $this->db->table('quiz_answers')->selectSum('score', 'total')->where('attemptid', $attemptId)->get()->getRowArray();
        $total = round((float) ($row['total'] ?? 0), 2);
        $this->db->table('quiz_attempts')->where('id', $attemptId)->update(['score' => $total]);

        return $total;
    }

    public function finalize(int $attemptId, string $status, ?string $reason = null): void
    {
        $a = $this->db->table('quiz_attempts')->where('id', $attemptId)->get()->getRowArray();
        if (! $a || $a['status'] !== 'in_progress') {
            return;
        }

        $this->db->table('quiz_attempts')->where('id', $attemptId)->update([
            'status'       => $status,
            'auto_reason'  => $reason,
            'submitted_at' => $this->now(),
        ]);
        $this->recompute($attemptId);
        $this->log($attemptId, $status === 'submitted' ? 'submitted' : 'auto_submitted', $reason);
    }

    /** Submits the attempt if its deadline has passed. Returns the (possibly updated) row. */
    public function expireIfNeeded(array $a): array
    {
        if ($a['status'] === 'in_progress' && $a['deadline_at'] && time() > strtotime($a['deadline_at'])) {
            $this->finalize((int) $a['id'], 'auto_submitted', 'time_up');

            return $this->db->table('quiz_attempts')->where('id', $a['id'])->get()->getRowArray();
        }

        return $a;
    }

    /**
     * Logs an event and applies the warning policy.
     * @return array{0: int, 1: bool, 2: bool} [violations, counted, terminated]
     */
    public function registerEvent(array $a, string $type, ?string $detail, int $maxViolations): array
    {
        $this->log((int) $a['id'], $type, $detail);

        $violations = (int) $a['violations'];
        $counted    = false;

        if (in_array($type, self::VIOLATION_EVENTS, true)) {
            $last = $a['last_violation_at'] ? strtotime($a['last_violation_at']) : 0;
            if (time() - $last >= self::VIOLATION_GAP_SECONDS) {
                $violations++;
                $counted = true;
                $this->db->table('quiz_attempts')->where('id', $a['id'])->update([
                    'violations'        => $violations,
                    'last_violation_at' => $this->now(),
                ]);
            }
        }

        $terminated = $counted && $maxViolations > 0 && $violations >= $maxViolations;
        if ($terminated) {
            $this->finalize((int) $a['id'], 'auto_submitted', 'too_many_violations');
        }

        return [$violations, $counted, $terminated];
    }
}
