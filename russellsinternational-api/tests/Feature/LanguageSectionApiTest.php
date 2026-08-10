<?php

namespace Tests\Feature;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSectionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The Task 3 migration seeds the three real sections. They carry no
        // programs so the endpoint omits them, but clearing them keeps the slug
        // and count assertions below unambiguous.
        LanguageSection::query()->delete();
    }

    /**
     * The slug is passed explicitly: it is generated from the label otherwise, and
     * these tests assert on exact slugs.
     */
    private function section(array $overrides = []): LanguageSection
    {
        return LanguageSection::create($overrides + [
            'slug' => 'english',
            'label' => 'English Tests',
            'short_label' => 'English',
            'heading' => 'English Test Preparation',
            'subtitle' => 'IELTS, PTE and more.',
            'icon_name' => 'Languages',
            'color_class' => 'bg-blue-50 text-blue-600',
            'sort_order' => 1,
        ]);
    }

    private function program(LanguageSection $section, array $overrides = []): LanguageProgram
    {
        return LanguageProgram::create($overrides + [
            'language_section_id' => $section->id,
            'flag_emoji' => 'GB',
            'title' => 'IELTS Preparation',
            'duration' => '8 Weeks',
            'badge' => 'Most Popular',
            'description' => 'Complete coaching.',
            'benefits' => ['Band score strategy'],
            'color_class' => 'bg-blue-50 text-blue-600',
            'sort_order' => 1,
        ]);
    }

    public function test_it_returns_sections_with_their_programs_nested(): void
    {
        $english = $this->section();
        $this->program($english);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'english')
            ->assertJsonPath('data.0.label', 'English Tests')
            ->assertJsonPath('data.0.tab_label', 'English')
            ->assertJsonPath('data.0.heading', 'English Test Preparation')
            ->assertJsonPath('data.0.icon_name', 'Languages')
            ->assertJsonPath('data.0.color_class', 'bg-blue-50 text-blue-600')
            ->assertJsonCount(1, 'data.0.programs')
            ->assertJsonPath('data.0.programs.0.title', 'IELTS Preparation')
            ->assertJsonPath('data.0.programs.0.benefits.0', 'Band score strategy');
    }

    public function test_sections_are_ordered_and_hidden_sections_are_excluded(): void
    {
        $third = $this->section(['slug' => 'korean', 'label' => 'Korean Tests', 'sort_order' => 30]);
        $hidden = $this->section(['slug' => 'hidden', 'label' => 'Hidden Tests', 'sort_order' => 1, 'is_active' => false]);
        $first = $this->section(['slug' => 'german', 'label' => 'German Tests', 'sort_order' => 10]);

        $this->program($third, ['title' => 'TOPIK']);
        $this->program($hidden, ['title' => 'Hidden Program']);
        $this->program($first, ['title' => 'Goethe']);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.label', 'German Tests')
            ->assertJsonPath('data.1.label', 'Korean Tests')
            ->assertJsonMissing(['label' => 'Hidden Tests']);
    }

    public function test_a_section_with_no_active_programs_is_omitted_so_visitors_never_see_an_empty_tab(): void
    {
        $withProgram = $this->section(['label' => 'English Tests', 'sort_order' => 1]);
        $this->program($withProgram);

        $empty = $this->section(['slug' => 'arabic', 'label' => 'Arabic Tests', 'sort_order' => 2]);
        $this->program($empty, ['title' => 'Inactive ALPT', 'is_active' => false]);

        $this->section(['slug' => 'french', 'label' => 'French Tests', 'sort_order' => 3]); // no programs at all

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.label', 'English Tests');
    }

    public function test_inactive_programs_are_excluded_from_a_visible_section(): void
    {
        $english = $this->section();
        $this->program($english, ['title' => 'Visible', 'sort_order' => 2]);
        $this->program($english, ['title' => 'Hidden', 'is_active' => false, 'sort_order' => 1]);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.programs')
            ->assertJsonPath('data.0.programs.0.title', 'Visible');
    }

    public function test_tab_label_falls_back_to_the_label_in_the_response(): void
    {
        $section = $this->section(['label' => 'Arabic Tests', 'short_label' => null]);
        $this->program($section, ['title' => 'ALPT']);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonPath('data.0.tab_label', 'Arabic Tests');
    }

    public function test_programs_are_ordered_by_sort_order_within_a_section(): void
    {
        $english = $this->section();
        $this->program($english, ['title' => 'Second', 'sort_order' => 20]);
        $this->program($english, ['title' => 'First', 'sort_order' => 10]);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonPath('data.0.programs.0.title', 'First')
            ->assertJsonPath('data.0.programs.1.title', 'Second');
    }

    public function test_it_returns_an_empty_list_rather_than_failing_when_nothing_is_configured(): void
    {
        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }
}
