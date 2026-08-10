<?php

namespace Tests\Feature;

use App\Models\LanguageSection;
use Database\Seeders\LanguageSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSectionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_the_label(): void
    {
        $section = LanguageSection::create([
            'label' => 'Arabic Tests',
            'heading' => 'Arabic Language & Exams',
        ]);

        $this->assertSame('arabic-tests', $section->slug);
    }

    public function test_duplicate_labels_get_distinct_slugs(): void
    {
        LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'One']);
        $second = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'Two']);

        $this->assertSame('arabic-tests-2', $second->slug);
    }

    public function test_an_explicit_slug_is_kept(): void
    {
        $section = LanguageSection::create([
            'slug' => 'my-english',
            'label' => 'English Tests',
            'heading' => 'English Test Preparation',
        ]);

        $this->assertSame('my-english', $section->slug);
    }

    public function test_tab_label_falls_back_to_the_label_when_short_label_is_blank(): void
    {
        $withShort = LanguageSection::create(['label' => 'German Programs', 'short_label' => 'German', 'heading' => 'H']);
        $without = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        $this->assertSame('German', $withShort->tab_label);
        $this->assertSame('Arabic Tests', $without->tab_label);
        $this->assertNull($without->short_label, 'The raw column must stay blank so the admin field is honest.');
    }

    public function test_active_scope_hides_inactive_and_sorts_by_order(): void
    {
        LanguageSection::create(['label' => 'Zeta Scope', 'heading' => 'H', 'sort_order' => 30]);
        LanguageSection::create(['label' => 'Hidden Scope', 'heading' => 'H', 'sort_order' => 1, 'is_active' => false]);
        LanguageSection::create(['label' => 'Alpha Scope', 'heading' => 'H', 'sort_order' => 10]);

        // Scoped to this test's own records: the Task 3 migration seeds the three
        // real sections into every test database, so an exact whole-table
        // assertion here would be brittle.
        $labels = LanguageSection::active()
            ->get()
            ->pluck('label')
            ->filter(fn (string $label) => str_contains($label, 'Scope'))
            ->values()
            ->all();

        $this->assertSame(['Alpha Scope', 'Zeta Scope'], $labels);
    }

    public function test_seeder_creates_the_three_current_sections_and_is_idempotent(): void
    {
        (new LanguageSectionSeeder)->run();
        (new LanguageSectionSeeder)->run();

        $this->assertSame(3, LanguageSection::count());

        $english = LanguageSection::where('slug', 'english')->firstOrFail();
        $this->assertSame('English Tests', $english->label);
        $this->assertSame('English', $english->short_label);
        $this->assertSame('English Test Preparation', $english->heading);
        $this->assertSame('Languages', $english->icon_name);
        $this->assertSame(1, $english->sort_order);

        $this->assertSame(
            ['english', 'german', 'korean'],
            LanguageSection::active()->pluck('slug')->all()
        );
    }
}
