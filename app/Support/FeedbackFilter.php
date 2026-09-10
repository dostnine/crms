<?php

namespace App\Support;

/**
 * Cleans respondent free-text feedback for the printed CSI reports.
 *
 * Most respondents leave the comment box as a non-answer ("None", "n/a", "-",
 * "."). Printing those wastes pages and buries the feedback that actually says
 * something, so they are dropped from every report listing. Kept here rather
 * than in each Blade template so all reports filter identically.
 */
class FeedbackFilter
{
    /**
     * Normalized forms that count as "the respondent didn't really answer".
     */
    private const PLACEHOLDERS = [
        '', 'n/a', 'na', 'n.a', 'not applicable', 'none', 'none at all',
        'non so far', 'none so far', 'nothing', 'no', 'no comment',
        'no comments', 'nil', 'wala', 'walang', 'walanaman', 'none po',
        'nope', 'nada', 'x', 'xx', 'test', 'asd', 'abc',
    ];

    /**
     * Strip case, surrounding whitespace and decorative punctuation so
     * "None." , "n/a " and "-" all collapse onto the same placeholder.
     */
    public static function normalize($text): string
    {
        $t = mb_strtolower(trim((string) $text));

        return trim($t, " \t\n\r\0\x0B.,;:!?-–—/\\*'\"()[]{}");
    }

    /**
     * Is this real feedback worth printing?
     */
    public static function isMeaningful($text): bool
    {
        $t = self::normalize($text);

        if ($t === '' || in_array($t, self::PLACEHOLDERS, true)) {
            return false;
        }

        // Anything down to a couple of characters is noise, not feedback.
        return mb_strlen($t) > 2;
    }

    /**
     * Blank out placeholder values in metadata columns ("N/A" unit, PSTO...).
     * An empty cell reads cleaner in print than a column of "N/A".
     */
    public static function blankIfPlaceholder($value): string
    {
        $v = trim((string) $value);
        $n = self::normalize($v);

        return ($n === '' || in_array($n, ['n/a', 'na', 'n.a', 'none', 'not applicable'], true))
            ? ''
            : $v;
    }

    /**
     * Filter a comment collection down to meaningful entries and normalize
     * each row's shape. Accepts plain strings or the array/object rows the
     * report queries produce.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function clean($comments): array
    {
        $rows = [];

        foreach (($comments ?? []) as $comment) {
            if (is_object($comment)) {
                $comment = json_decode(json_encode($comment), true);
            }
            if (!is_array($comment)) {
                $comment = ['text' => $comment];
            }

            $text = $comment['text'] ?? $comment['comment'] ?? '';

            if (!self::isMeaningful($text)) {
                continue;
            }

            $rows[] = [
                'text' => trim((string) $text),
                'unit' => self::blankIfPlaceholder($comment['unit_name'] ?? $comment['unit'] ?? ''),
                'subUnit' => self::blankIfPlaceholder($comment['sub_unit_name'] ?? $comment['sub_unit'] ?? ''),
                'psto' => self::blankIfPlaceholder($comment['psto_name'] ?? $comment['psto'] ?? ''),
                'date' => $comment['date'] ?? $comment['created_at'] ?? '',
                'isComplaint' => (bool) ($comment['is_complaint'] ?? false),
            ];
        }

        return $rows;
    }

    /**
     * Cleaned rows split into complaints first, then comments -- the order the
     * reports print them in.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    public static function split($comments): array
    {
        $rows = self::clean($comments);

        return [
            array_values(array_filter($rows, fn ($r) => $r['isComplaint'])),
            array_values(array_filter($rows, fn ($r) => !$r['isComplaint'])),
        ];
    }
}
