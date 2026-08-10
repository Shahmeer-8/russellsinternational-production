<?php

namespace Database\Seeders;

use App\Models\LanguageSection;
use App\Support\LegacyLanguageCodeMap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LanguageProgramSectionBackfillSeeder extends Seeder
{
    /**
     * Points every program at a section. Idempotent: only rows with no section are
     * touched, so re-running cannot move content the owner has since re-filed.
     */
    public function run(): void
    {
        (new LanguageSectionSeeder)->run();

        $slugToId = LanguageSection::query()->pluck('id', 'slug');
        $fallbackId = $slugToId[LegacyLanguageCodeMap::FALLBACK_SLUG]
            ?? LanguageSection::query()->orderBy('sort_order')->value('id');

        if (! $fallbackId) {
            return;
        }

        $hasLegacyColumn = Schema::hasColumn('language_programs', 'language_code');
        $columns = $hasLegacyColumn ? ['id', 'language_code'] : ['id'];

        foreach (DB::table('language_programs')->whereNull('language_section_id')->get($columns) as $program) {
            $slug = $hasLegacyColumn
                ? LegacyLanguageCodeMap::slugFor($program->language_code)
                : LegacyLanguageCodeMap::FALLBACK_SLUG;

            DB::table('language_programs')
                ->where('id', $program->id)
                ->update(['language_section_id' => $slugToId[$slug] ?? $fallbackId]);
        }
    }
}
