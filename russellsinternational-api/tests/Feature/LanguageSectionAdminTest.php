<?php

namespace Tests\Feature;

use App\Filament\Resources\LanguageSectionResource\Pages\CreateLanguageSection;
use App\Filament\Resources\LanguageSectionResource\Pages\EditLanguageSection;
use App\Filament\Resources\LanguageSectionResource\Pages\ListLanguageSections;
use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use App\Models\User;
use App\Support\AdminChoices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageSectionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-language-admin@example.com',
            'password' => 'password',
        ]));
    }

    public function test_the_list_page_renders(): void
    {
        Livewire::test(ListLanguageSections::class)->assertSuccessful();
    }

    public function test_an_owner_can_create_a_new_language_section(): void
    {
        Livewire::test(CreateLanguageSection::class)
            ->fillForm([
                'label' => 'Arabic Tests',
                'short_label' => 'Arabic',
                'heading' => 'Arabic Language & Exams',
                'subtitle' => 'ALPT and practical Arabic for work and study.',
                'icon_name' => 'Globe',
                'color_class' => 'bg-emerald-50 text-emerald-600',
                'sort_order' => 4,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $section = LanguageSection::where('label', 'Arabic Tests')->first();

        $this->assertNotNull($section, 'The owner could not create a language section.');
        $this->assertSame('arabic-tests', $section->slug);
        $this->assertSame('Arabic', $section->short_label);
        $this->assertSame('Globe', $section->icon_name);
        $this->assertSame('bg-emerald-50 text-emerald-600', $section->color_class);
        $this->assertSame(4, $section->sort_order);
    }

    public function test_a_section_can_be_created_without_a_short_label(): void
    {
        Livewire::test(CreateLanguageSection::class)
            ->fillForm([
                'label' => 'French Tests',
                'heading' => 'French Language & Exams',
                'icon_name' => 'Globe',
                'color_class' => 'bg-blue-50 text-blue-600',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $section = LanguageSection::where('label', 'French Tests')->firstOrFail();

        $this->assertNull($section->short_label);
        $this->assertSame('French Tests', $section->tab_label);
    }

    public function test_label_and_heading_are_required(): void
    {
        Livewire::test(CreateLanguageSection::class)
            ->fillForm(['label' => '', 'heading' => ''])
            ->call('create')
            ->assertHasFormErrors(['label' => 'required', 'heading' => 'required']);
    }

    public function test_a_section_can_be_edited(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'Old heading']);

        Livewire::test(EditLanguageSection::class, ['record' => $section->getKey()])
            ->fillForm(['heading' => 'New heading'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New heading', $section->refresh()->heading);
    }

    public function test_an_empty_section_can_be_deleted(): void
    {
        $section = LanguageSection::create(['label' => 'Unused Tests', 'heading' => 'H']);

        Livewire::test(EditLanguageSection::class, ['record' => $section->getKey()])
            ->callAction('delete');

        $this->assertModelMissing($section);
    }

    public function test_deleting_a_section_that_still_has_programs_is_blocked(): void
    {
        $section = LanguageSection::create(['label' => 'Busy Tests', 'heading' => 'H']);
        LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'GB',
            'title' => 'IELTS Preparation',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => [],
        ]);

        Livewire::test(EditLanguageSection::class, ['record' => $section->getKey()])
            ->callAction('delete');

        // Without this guard the foreign key nulls out and the programs vanish
        // from the website with no warning.
        $this->assertModelExists($section);
        $this->assertDatabaseHas('language_programs', [
            'title' => 'IELTS Preparation',
            'language_section_id' => $section->id,
        ]);
    }

    public function test_the_icon_choices_match_the_frontend_registry(): void
    {
        $registryPath = base_path('../src/lib/icons.ts');

        if (! is_file($registryPath)) {
            $this->markTestSkipped('The frontend registry lives beside the nested API copy only.');
        }

        $registry = (string) file_get_contents($registryPath);

        foreach (array_keys(AdminChoices::icons()) as $icon) {
            $this->assertStringContainsString(
                $icon,
                $registry,
                "Icon [{$icon}] is offered in the admin but missing from src/lib/icons.ts, so it would render as the fallback."
            );
        }
    }

    public function test_every_colour_choice_is_a_tailwind_background_and_text_pair(): void
    {
        foreach (AdminChoices::colors() as $classes => $label) {
            $this->assertMatchesRegularExpression(
                '/^bg-[a-z]+-\d{2,3} text-[a-z]+-\d{2,3}$/',
                $classes,
                "Colour [{$label}] must be a 'bg-… text-…' pair or the frontend split produces broken classes."
            );
        }
    }
}
