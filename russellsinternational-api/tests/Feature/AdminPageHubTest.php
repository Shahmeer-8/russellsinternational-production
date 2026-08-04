<?php

namespace Tests\Feature;

use App\Filament\Pages\AboutPageContent;
use App\Filament\Pages\CareersPageContent;
use App\Filament\Pages\EventsPageContent;
use App\Filament\Pages\FooterContent;
use App\Filament\Pages\HeaderContent;
use App\Filament\Pages\HomePageContent;
use App\Filament\Pages\LanguagePageContent;
use App\Filament\Pages\SkillsPageContent;
use App\Filament\Pages\StudyAbroadPageContent;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The sidebar is built from these page hubs rather than from resources, so a hub
 * that throws takes out a whole area of the admin panel. Each one reads
 * page_sections/pages rows and renders preview images, so it also has to survive
 * those rows being absent.
 */
class AdminPageHubTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function pageHubProvider(): array
    {
        return [
            'Home Page' => [HomePageContent::class],
            'About Us Page' => [AboutPageContent::class],
            'Skills Page' => [SkillsPageContent::class],
            'Study Abroad Page' => [StudyAbroadPageContent::class],
            'Language Page' => [LanguagePageContent::class],
            'Careers Page' => [CareersPageContent::class],
            'Events Page' => [EventsPageContent::class],
            'Header' => [HeaderContent::class],
            'Footer' => [FooterContent::class],
        ];
    }

    private function loginAsAdmin(): void
    {
        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-hub-admin@example.com',
            'password' => 'password',
        ]));
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('pageHubProvider')]
    public function test_page_hub_renders_with_seeded_content(string $page): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->loginAsAdmin();

        Livewire::test($page)->assertSuccessful();
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('pageHubProvider')]
    public function test_page_hub_renders_on_an_empty_database(string $page): void
    {
        $this->loginAsAdmin();

        // With no page_sections or pages rows at all, hubs must show "Missing"
        // placeholders and link to create screens instead of erroring.
        Livewire::test($page)->assertSuccessful();
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('pageHubProvider')]
    public function test_page_hub_is_reachable_over_http(string $page): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->loginAsAdmin();

        $this->get($page::getUrl())->assertSuccessful();
    }

    public function test_page_hubs_are_guarded_by_authentication(): void
    {
        foreach (self::pageHubProvider() as $label => [$page]) {
            $this->get($page::getUrl())
                ->assertRedirect(route('filament.admin.auth.login'));
        }
    }
}
