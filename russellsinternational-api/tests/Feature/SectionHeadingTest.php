<?php

namespace Tests\Feature;

use App\Models\PageSection;
use Database\Seeders\SectionHeadingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionHeadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_heading_for_every_section_that_used_to_be_hardcoded(): void
    {
        (new SectionHeadingSeeder)->run();

        $expected = [
            ['home', 'why_choose_us'],
            ['home', 'testimonials'],
            ['home', 'news'],
            ['home', 'contact'],
            ['home', 'stats'],
            ['home', 'services'],
            ['skills', 'courses'],
            ['careers', 'jobs'],
            ['careers', 'internships'],
            ['events', 'news'],
            ['events', 'gallery'],
            ['languages', 'intro'],
            ['study-abroad', 'destinations'],
        ];

        foreach ($expected as [$page, $key]) {
            $this->assertDatabaseHas('page_sections', [
                'page_slug' => $page,
                'section_key' => $key,
                'is_active' => true,
            ]);
        }
    }

    public function test_seeded_text_matches_what_the_components_shipped_so_the_site_does_not_change(): void
    {
        (new SectionHeadingSeeder)->run();

        $why = PageSection::where('page_slug', 'home')->where('section_key', 'why_choose_us')->firstOrFail();
        $this->assertSame("Why Russell's International", $why->eyebrow);
        $this->assertSame('Your Trusted Partner in Growth', $why->title);

        $languages = PageSection::where('page_slug', 'languages')->where('section_key', 'intro')->firstOrFail();
        $this->assertSame('Language Programs', $languages->eyebrow);
        $this->assertSame('Speak the World', $languages->title);
        $this->assertSame(
            'Exam-focused language training for study abroad, visa pathways, work routes and global careers.',
            $languages->subtitle
        );
    }

    public function test_running_it_twice_creates_no_duplicates(): void
    {
        (new SectionHeadingSeeder)->run();
        $count = PageSection::count();

        (new SectionHeadingSeeder)->run();

        $this->assertSame($count, PageSection::count());
    }

    public function test_it_never_overwrites_a_heading_the_owner_edited(): void
    {
        (new SectionHeadingSeeder)->run();

        $section = PageSection::where('page_slug', 'home')->where('section_key', 'why_choose_us')->firstOrFail();
        $section->update(['title' => 'OWNER WROTE THIS']);

        (new SectionHeadingSeeder)->run();

        $this->assertSame('OWNER WROTE THIS', $section->refresh()->title);
    }

    public function test_the_headings_are_exposed_through_the_page_sections_endpoint(): void
    {
        (new SectionHeadingSeeder)->run();

        $this->getJson('/api/v1/pages/home/sections')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.why_choose_us.title', 'Your Trusted Partner in Growth')
            ->assertJsonPath('data.testimonials.eyebrow', 'Student Stories');

        $this->getJson('/api/v1/pages/careers/sections')
            ->assertOk()
            ->assertJsonPath('data.jobs.title', 'Join Our Team or Our Partners')
            ->assertJsonPath('data.internships.eyebrow', 'Internships');
    }

    public function test_a_hidden_heading_is_not_returned_so_the_component_falls_back(): void
    {
        (new SectionHeadingSeeder)->run();

        PageSection::where('page_slug', 'home')->where('section_key', 'why_choose_us')->update(['is_active' => false]);

        $this->getJson('/api/v1/pages/home/sections')
            ->assertOk()
            ->assertJsonMissingPath('data.why_choose_us');
    }
}
