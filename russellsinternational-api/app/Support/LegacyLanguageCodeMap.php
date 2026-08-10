<?php

namespace App\Support;

/**
 * Maps the retired `language_code` values onto section slugs. `ielts`, `pte`,
 * `toefl` and `languagecert` fold into English because that is already how the
 * frontend grouped them before sections existed.
 *
 * Kept as a pure map so it stays testable after the column is dropped.
 */
class LegacyLanguageCodeMap
{
    private const CODE_TO_SLUG = [
        'english' => 'english',
        'ielts' => 'english',
        'pte' => 'english',
        'toefl' => 'english',
        'languagecert' => 'english',
        'german' => 'german',
        'goethe' => 'german',
        'testdaf' => 'german',
        'telc' => 'german',
        'korean' => 'korean',
        'topik' => 'korean',
        'eps-topik' => 'korean',
    ];

    public const FALLBACK_SLUG = 'english';

    /**
     * The section slug a legacy code belongs to. Unknown and blank codes fall back
     * to English rather than silently landing in Korean, which is what the old
     * frontend normalizeGroup() did.
     */
    public static function slugFor(?string $code): string
    {
        return self::CODE_TO_SLUG[strtolower(trim((string) $code))] ?? self::FALLBACK_SLUG;
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::CODE_TO_SLUG;
    }
}
