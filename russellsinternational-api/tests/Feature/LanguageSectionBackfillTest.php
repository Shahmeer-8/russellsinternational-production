<?php

namespace Tests\Feature;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use App\Support\LegacyLanguageCodeMap;
use Database\Seeders\LanguageProgramSectionBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LanguageSectionBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_program_belongs_to_a_section(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        $program = LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'SA',
            'title' => 'ALPT Preparation',
            'duration' => '8 Weeks',
            'badge' => 'New',
            'description' => 'Arabic proficiency coaching.',
            'benefits' => ['Reading', 'Writing'],
        ]);

        $this->assertSame($section->id, $program->section->id);
        $this->assertTrue($section->programs->contains($program));
    }

    public function test_deleting_a_section_nulls_the_foreign_key_rather_than_deleting_programs(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);
        $program = LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'SA',
            'title' => 'ALPT Preparation',
            'duration' => '8 Weeks',
            'badge' => 'New',
            'description' => 'D',
            'benefits' => [],
        ]);

        $section->delete();

        $this->assertDatabaseHas('language_programs', ['id' => $program->id, 'language_section_id' => null]);
    }

    public function test_icon_name_is_optional(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        $program = LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'SA',
            'title' => 'With Icon',
            'duration' => '8 Weeks',
            'badge' => 'New',
            'description' => 'D',
            'benefits' => [],
            'icon_name' => 'ScrollText',
        ]);

        $this->assertSame('ScrollText', $program->icon_name);
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('language_programs', 'icon_name'));
    }

    public function test_legacy_codes_map_to_the_right_section_with_ielts_folded_into_english(): void
    {
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('english'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('ielts'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('IELTS'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor(' pte '));
        $this->assertSame('german', LegacyLanguageCodeMap::slugFor('goethe'));
        $this->assertSame('korean', LegacyLanguageCodeMap::slugFor('eps-topik'));
    }

    public function test_an_unknown_or_blank_code_falls_back_to_english_not_korean(): void
    {
        // The old frontend normalizeGroup() dumped anything unrecognised into
        // Korean. English is the safe default and matches the fallback section.
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('arabic'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor(''));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor(null));
    }

    public function test_the_backfill_seeder_assigns_every_program_a_section(): void
    {
        (new LanguageProgramSectionBackfillSeeder)->run();

        $english = LanguageSection::where('slug', 'english')->firstOrFail();

        // Created without a section, so the seeder must file it under the fallback.
        $orphan = LanguageProgram::create([
            'flag_emoji' => 'XX',
            'title' => 'Orphaned Program',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => [],
        ]);

        $this->assertNull($orphan->language_section_id);

        (new LanguageProgramSectionBackfillSeeder)->run();

        $this->assertSame($english->id, $orphan->refresh()->language_section_id);
        $this->assertSame(0, LanguageProgram::whereNull('language_section_id')->count());
    }

    public function test_the_backfill_seeder_never_refiles_a_program_that_already_has_a_section(): void
    {
        (new LanguageProgramSectionBackfillSeeder)->run();

        $korean = LanguageSection::where('slug', 'korean')->firstOrFail();
        $program = LanguageProgram::create([
            'language_section_id' => $korean->id,
            'flag_emoji' => 'KR',
            'title' => 'Deliberately Filed',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => [],
        ]);

        (new LanguageProgramSectionBackfillSeeder)->run();

        $this->assertSame($korean->id, $program->refresh()->language_section_id);
    }
}
