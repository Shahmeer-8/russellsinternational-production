<?php

namespace Tests\Feature;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Database\Seeders\LanguageProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageProgramSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_the_eight_former_hardcoded_programs(): void
    {
        (new LanguageProgramSeeder)->run();

        $this->assertSame(8, LanguageProgram::count());

        $english = LanguageSection::where('slug', 'english')->firstOrFail();
        $ielts = LanguageProgram::where('title', 'IELTS Preparation')->firstOrFail();

        $this->assertSame($english->id, $ielts->language_section_id);
        $this->assertSame('8 Weeks', $ielts->duration);
        $this->assertSame('Most Popular', $ielts->badge);
        $this->assertSame('Languages', $ielts->icon_name);
        $this->assertSame(
            ['Band score strategy', 'Writing task feedback', 'Speaking interview practice', 'Full-length mock exams'],
            $ielts->benefits
        );

        $this->assertSame(3, LanguageProgram::where('language_section_id', $english->id)->count());
        $this->assertSame(3, LanguageProgram::where('language_section_id', LanguageSection::where('slug', 'german')->value('id'))->count());
        $this->assertSame(2, LanguageProgram::where('language_section_id', LanguageSection::where('slug', 'korean')->value('id'))->count());
    }

    public function test_running_it_twice_creates_no_duplicates(): void
    {
        (new LanguageProgramSeeder)->run();
        (new LanguageProgramSeeder)->run();

        $this->assertSame(8, LanguageProgram::count());
        $this->assertSame(1, LanguageProgram::where('title', 'IELTS Preparation')->count());
    }

    public function test_it_never_overwrites_a_program_the_owner_already_edited(): void
    {
        // The Task 3 migration already seeded the three real sections.
        $english = LanguageSection::where('slug', 'english')->firstOrFail();

        LanguageProgram::create([
            'language_section_id' => $english->id,
            'flag_emoji' => 'GB',
            'title' => 'IELTS Preparation',
            'duration' => 'OWNER EDITED',
            'badge' => 'Owner Badge',
            'description' => 'Owner wrote this.',
            'benefits' => ['Owner benefit'],
        ]);

        (new LanguageProgramSeeder)->run();

        $this->assertSame(1, LanguageProgram::where('title', 'IELTS Preparation')->count());
        $this->assertSame('OWNER EDITED', LanguageProgram::where('title', 'IELTS Preparation')->value('duration'));
    }

    public function test_it_removes_the_lorem_test_record(): void
    {
        // The Task 3 migration already seeded the three real sections.
        $english = LanguageSection::where('slug', 'english')->firstOrFail();

        LanguageProgram::create([
            'language_section_id' => $english->id,
            'flag_emoji' => 'XX',
            'title' => 'Acton Kim',
            'duration' => 'Quam Nam cillum dolo',
            'badge' => 'Officiis enim labore',
            'description' => 'Qui unde mollit est',
            'benefits' => [],
        ]);

        (new LanguageProgramSeeder)->run();

        $this->assertSame(0, LanguageProgram::where('title', 'Acton Kim')->count());
    }

    public function test_no_migration_imports_this_data_so_test_databases_stay_clean(): void
    {
        // RefreshDatabase runs every migration before every test. Importing these
        // programs from a migration would collide with ContentLifecycleTest's
        // language-program fixture on sort_order and make its ordering assertion
        // flaky, so the import must stay an explicit call.
        $this->assertSame(0, LanguageProgram::count());
    }
}
