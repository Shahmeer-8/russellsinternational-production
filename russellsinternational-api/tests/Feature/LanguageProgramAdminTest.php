<?php

namespace Tests\Feature;

use App\Filament\Resources\LanguageProgramResource\Pages\CreateLanguageProgram;
use App\Filament\Resources\LanguageProgramResource\Pages\EditLanguageProgram;
use App\Filament\Resources\LanguageProgramResource\Pages\ListLanguagePrograms;
use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageProgramAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-program-admin@example.com',
            'password' => 'password',
        ]));
    }

    public function test_the_list_page_renders(): void
    {
        Livewire::test(ListLanguagePrograms::class)->assertSuccessful();
    }

    public function test_a_program_is_created_against_a_section(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        Livewire::test(CreateLanguageProgram::class)
            ->fillForm([
                'language_section_id' => $section->id,
                'flag_emoji' => 'SA',
                'title' => 'ALPT Preparation',
                'duration' => '8 Weeks',
                'badge' => 'New',
                'description' => 'Arabic proficiency coaching.',
                'color_class' => 'bg-emerald-50 text-emerald-600',
                'icon_name' => 'Globe',
                'benefits' => [['item' => 'Reading practice']],
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $program = LanguageProgram::where('title', 'ALPT Preparation')->firstOrFail();

        $this->assertSame($section->id, $program->language_section_id);
        $this->assertSame('bg-emerald-50 text-emerald-600', $program->color_class);
        $this->assertSame('Globe', $program->icon_name);
        $this->assertSame(['Reading practice'], $program->benefits);
    }

    public function test_a_section_is_required_so_a_program_cannot_become_invisible(): void
    {
        Livewire::test(CreateLanguageProgram::class)
            ->fillForm([
                'flag_emoji' => 'SA',
                'title' => 'Orphan Program',
                'duration' => '8 Weeks',
                'badge' => 'New',
                'description' => 'D',
                'benefits' => [['item' => 'A point']],
            ])
            ->call('create')
            ->assertHasFormErrors(['language_section_id' => 'required']);
    }

    public function test_a_program_saves_with_a_single_benefit_so_the_owner_is_not_forced_to_write_four(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        Livewire::test(CreateLanguageProgram::class)
            ->fillForm([
                'language_section_id' => $section->id,
                'flag_emoji' => 'SA',
                'title' => 'Single Benefit Program',
                'duration' => '8 Weeks',
                'badge' => 'New',
                'description' => 'D',
                'benefits' => [['item' => 'Only one point']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['Only one point'],
            LanguageProgram::where('title', 'Single Benefit Program')->firstOrFail()->benefits
        );
    }

    public function test_icon_is_optional_and_falls_back_to_the_section_icon_on_the_website(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H', 'icon_name' => 'Globe']);

        Livewire::test(CreateLanguageProgram::class)
            ->fillForm([
                'language_section_id' => $section->id,
                'flag_emoji' => 'SA',
                'title' => 'No Icon Program',
                'duration' => '8 Weeks',
                'badge' => 'New',
                'description' => 'D',
                'benefits' => [['item' => 'A point']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(LanguageProgram::where('title', 'No Icon Program')->firstOrFail()->icon_name);
    }

    public function test_a_program_can_be_moved_to_another_section(): void
    {
        $from = LanguageSection::create(['label' => 'From Tests', 'heading' => 'H']);
        $to = LanguageSection::create(['label' => 'To Tests', 'heading' => 'H']);

        $program = LanguageProgram::create([
            'language_section_id' => $from->id,
            'flag_emoji' => 'XX',
            'title' => 'Movable Program',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => ['A point'],
        ]);

        Livewire::test(EditLanguageProgram::class, ['record' => $program->getKey()])
            ->fillForm(['language_section_id' => $to->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($to->id, $program->refresh()->language_section_id);
    }
}
