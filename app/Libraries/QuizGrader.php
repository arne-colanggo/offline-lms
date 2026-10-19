<?php

namespace App\Libraries;

/** Grades one answer on the server. Correct answers never leave the server while the quiz is running. */
class QuizGrader
{
    public static function norm($s): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', (string) $s)));
    }

    public static function blanks(string $text): int
    {
        return (int) preg_match_all('/_{3,}/', $text);
    }

    /**
     * @param array $q       question row
     * @param array $options option rows of the question, ordered by id
     * @return array{0: ?float, 1: ?int} [score 0..1, is_correct]; [null, null] = needs manual grading
     */
    public static function grade(array $q, array $options, $answer): array
    {
        switch ($q['option_type']) {
            case 'MC':
            case 'TF':
                foreach ($options as $o) {
                    if ((int) $o['id'] === (int) $answer) {
                        $ok = $o['isanswer'] === 'T';
                        return [$ok ? 1.0 : 0.0, $ok ? 1 : 0];
                    }
                }
                return [0.0, 0];

            case 'ID':
                $given = self::norm(is_string($answer) ? $answer : '');
                if ($given !== '') {
                    foreach ($options as $o) {
                        if ($o['isanswer'] === 'T' && self::norm($o['description']) === $given) {
                            return [1.0, 1];
                        }
                    }
                }
                return [0.0, 0];

            case 'FB':
                $correct = array_values(array_filter($options, static fn ($o) => $o['isanswer'] === 'T'));
                $given   = is_array($answer) ? array_values($answer) : [];
                $hit     = 0;
                foreach ($correct as $i => $o) {
                    if (self::norm($given[$i] ?? '') === self::norm($o['description'])) {
                        $hit++;
                    }
                }
                $n = max(1, count($correct));
                return [round($hit / $n, 2), $hit === count($correct) ? 1 : 0];

            case 'MT':
                $given = is_array($answer) ? $answer : [];
                $hit   = 0;
                foreach ($options as $o) {
                    if (isset($given[$o['id']]) && self::norm($given[$o['id']]) === self::norm($o['match_text'])) {
                        $hit++;
                    }
                }
                $n = max(1, count($options));
                return [round($hit / $n, 2), $hit === count($options) ? 1 : 0];
        }

        return [null, null]; // ES
    }

    /** Human readable version of what the student answered (teacher view / review). */
    public static function answerText(array $q, array $options, $answer): string
    {
        switch ($q['option_type']) {
            case 'MC':
            case 'TF':
                foreach ($options as $o) {
                    if ((int) $o['id'] === (int) $answer) {
                        return (string) $o['description'];
                    }
                }
                return '';
            case 'FB':
                return is_array($answer) ? implode(' | ', array_map('strval', $answer)) : '';
            case 'MT':
                $out = [];
                foreach ($options as $o) {
                    $out[] = $o['description'] . ' → ' . (is_array($answer) ? ($answer[$o['id']] ?? '—') : '—');
                }
                return implode('; ', $out);
        }

        return is_string($answer) ? $answer : '';
    }

    public static function correctText(array $q, array $options): string
    {
        switch ($q['option_type']) {
            case 'MC':
            case 'TF':
                return implode(' / ', array_column(array_filter($options, static fn ($o) => $o['isanswer'] === 'T'), 'description'));
            case 'ID':
                return implode(' / ', array_column($options, 'description'));
            case 'FB':
                return implode(' | ', array_column($options, 'description'));
            case 'MT':
                return implode('; ', array_map(static fn ($o) => $o['description'] . ' → ' . $o['match_text'], $options));
        }

        return '(graded by the teacher)';
    }
}
