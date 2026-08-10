<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\HeroSlide;
use App\Models\Internship;
use App\Models\Job;
use App\Models\LanguageProgram;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\StudyDestination;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\TickerItem;
use App\Models\WhyChooseUsItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_tables_follow_create_update_inactive_delete_lifecycle(): void
    {
        foreach ($this->publicContentCases() as $case) {
            /** @var class-string<Model> $model */
            $model = $case['model'];
            $record = $model::create($case['attributes']);

            $this->assertDatabaseHas($record->getTable(), [$case['field'] => $case['initial']]);
            $this->assertEndpointContains($case['endpoint'], $case['initial'], $case['path']);

            $record->update([$case['field'] => $case['updated']]);
            $this->assertDatabaseHas($record->getTable(), [$case['field'] => $case['updated']]);
            $this->assertEndpointContains($case['endpoint'], $case['updated'], $case['path']);

            if (array_key_exists('is_active', $record->getAttributes())) {
                $record->update(['is_active' => false]);
                $this->assertEndpointMissing($case['endpoint'], $case['updated']);
            }

            $record->delete();
            $this->assertDatabaseMissing($record->getTable(), ['id' => $record->getKey()]);
            $this->assertEndpointMissing($case['endpoint'], $case['updated']);
        }
    }

    public function test_settings_pages_and_sections_lifecycle(): void
    {
        $setting = Setting::create([
            'group' => 'contact',
            'key' => 'qa_test_phone',
            'label' => 'QA Test Phone',
            'value' => 'QA_TEST_SETTING',
            'type' => 'text',
        ]);

        $this->getJson('/api/v1/settings/qa_test_phone')
            ->assertOk()
            ->assertJsonPath('data.value', 'QA_TEST_SETTING');

        $setting->update(['value' => 'QA_TEST_SETTING_UPDATED']);

        $this->getJson('/api/v1/settings/qa_test_phone')
            ->assertOk()
            ->assertJsonPath('data.value', 'QA_TEST_SETTING_UPDATED');

        $page = Page::create(['slug' => 'qa-test-page', 'name' => 'QA_TEST_PAGE', 'is_active' => true]);
        $section = PageSection::create([
            'page_slug' => 'qa-test-page',
            'section_key' => 'hero',
            'name' => 'QA Hero',
            'title' => 'QA_TEST_SECTION',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/pages/qa-test-page')->assertOk()->assertJsonPath('data.slug', 'qa-test-page');
        $this->getJson('/api/v1/pages/qa-test-page/sections')->assertOk()->assertJsonPath('data.hero.title', 'QA_TEST_SECTION');

        $section->update(['title' => 'QA_TEST_SECTION_UPDATED']);
        $this->getJson('/api/v1/pages/qa-test-page/sections')->assertOk()->assertJsonPath('data.hero.title', 'QA_TEST_SECTION_UPDATED');

        $section->update(['is_active' => false]);
        $this->getJson('/api/v1/pages/qa-test-page/sections')->assertOk()->assertJsonMissing(['title' => 'QA_TEST_SECTION_UPDATED']);

        $page->update(['is_active' => false]);
        $this->getJson('/api/v1/pages/qa-test-page')->assertNotFound();
    }

    /**
     * @return array<int, array{model: class-string<Model>, endpoint: string, path: string, field: string, initial: string, updated: string, attributes: array<string, mixed>}>
     */
    private function publicContentCases(): array
    {
        return [
            [
                'model' => HeroSlide::class,
                'endpoint' => '/api/v1/hero-slides',
                'path' => 'data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_HERO',
                'updated' => 'QA_TEST_HERO_UPDATED',
                'attributes' => [
                    'eyebrow' => 'QA',
                    'title' => 'QA_TEST_HERO',
                    'description' => 'QA description',
                    'cta_label' => 'Start',
                    'cta_url' => '/skills',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => TickerItem::class,
                'endpoint' => '/api/v1/ticker-items',
                'path' => 'data.0.text',
                'field' => 'text',
                'initial' => 'QA_TEST_TICKER',
                'updated' => 'QA_TEST_TICKER_UPDATED',
                'attributes' => ['text' => 'QA_TEST_TICKER', 'sort_order' => 1, 'is_active' => true],
            ],
            [
                'model' => Stat::class,
                'endpoint' => '/api/v1/stats',
                'path' => 'data.0.label',
                'field' => 'label',
                'initial' => 'QA_TEST_STAT',
                'updated' => 'QA_TEST_STAT_UPDATED',
                'attributes' => ['value' => '1', 'label' => 'QA_TEST_STAT', 'sort_order' => 1, 'is_active' => true],
            ],
            [
                'model' => Service::class,
                'endpoint' => '/api/v1/services',
                'path' => 'data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_SERVICE',
                'updated' => 'QA_TEST_SERVICE_UPDATED',
                'attributes' => [
                    'icon_name' => 'Code',
                    'title' => 'QA_TEST_SERVICE',
                    'description' => 'QA description',
                    'details' => 'QA details',
                    'color_class' => 'bg-blue-50 text-blue-600',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => WhyChooseUsItem::class,
                'endpoint' => '/api/v1/why-choose-us',
                'path' => 'data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_WHY',
                'updated' => 'QA_TEST_WHY_UPDATED',
                'attributes' => [
                    'icon_name' => 'Shield',
                    'title' => 'QA_TEST_WHY',
                    'description' => 'QA description',
                    'color_class' => 'bg-blue-50 text-blue-600',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => Course::class,
                'endpoint' => '/api/v1/courses',
                'path' => 'data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_COURSE',
                'updated' => 'QA_TEST_COURSE_UPDATED',
                'attributes' => [
                    'type' => 'paid',
                    'icon_name' => 'Code',
                    'title' => 'QA_TEST_COURSE',
                    'description' => 'QA description',
                    'duration' => '1 Week',
                    'students_count' => '1',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => StudyDestination::class,
                'endpoint' => '/api/v1/study-destinations',
                'path' => 'data.0.country',
                'field' => 'country',
                'initial' => 'QA_TEST_COUNTRY',
                'updated' => 'QA_TEST_COUNTRY_UPDATED',
                'attributes' => [
                    'flag_emoji' => 'QA',
                    'country' => 'QA_TEST_COUNTRY',
                    'partner_unis_count' => '1',
                    'highlight_unis' => 'QA University',
                    'intake_periods' => 'Jan',
                    'visa_success_rate' => '99%',
                    'description' => 'QA description',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => LanguageProgram::class,
                'endpoint' => '/api/v1/language-programs',
                'path' => 'data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_LANGUAGE',
                'updated' => 'QA_TEST_LANGUAGE_UPDATED',
                'attributes' => [
                    'flag_emoji' => 'QA',
                    'title' => 'QA_TEST_LANGUAGE',
                    'duration' => '1 Week',
                    'badge' => 'QA',
                    'description' => 'QA description',
                    'benefits' => ['QA benefit'],
                    'color_class' => 'bg-blue-50 text-blue-600',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => Job::class,
                'endpoint' => '/api/v1/jobs',
                'path' => 'data.data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_JOB',
                'updated' => 'QA_TEST_JOB_UPDATED',
                'attributes' => [
                    'title' => 'QA_TEST_JOB',
                    'company' => 'QA Company',
                    'location' => 'Remote',
                    'type' => 'Full-Time',
                    'description' => 'QA description',
                    'is_active' => true,
                ],
            ],
            [
                'model' => Internship::class,
                'endpoint' => '/api/v1/internships',
                'path' => 'data.data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_INTERNSHIP',
                'updated' => 'QA_TEST_INTERNSHIP_UPDATED',
                'attributes' => [
                    'title' => 'QA_TEST_INTERNSHIP',
                    'company' => 'QA Company',
                    'location' => 'Remote',
                    'duration' => '1 Month',
                    'type' => 'Remote',
                    'description' => 'QA description',
                    'is_active' => true,
                ],
            ],
            [
                'model' => Event::class,
                'endpoint' => '/api/v1/events',
                'path' => 'data.data.0.title',
                'field' => 'title',
                'initial' => 'QA_TEST_EVENT',
                'updated' => 'QA_TEST_EVENT_UPDATED',
                'attributes' => [
                    'content_type' => 'event',
                    'tag' => 'QA',
                    'tag_color' => 'bg-blue-50 text-blue-700',
                    'title' => 'QA_TEST_EVENT',
                    'event_date' => now()->addDay(),
                    'short_description' => 'QA description',
                    'is_active' => true,
                ],
            ],
            [
                'model' => GalleryPhoto::class,
                'endpoint' => '/api/v1/gallery',
                'path' => 'data.0.alt_text',
                'field' => 'alt_text',
                'initial' => 'QA_TEST_GALLERY',
                'updated' => 'QA_TEST_GALLERY_UPDATED',
                'attributes' => [
                    'image' => 'gallery/qa.jpg',
                    'alt_text' => 'QA_TEST_GALLERY',
                    'category' => 'campus',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => Testimonial::class,
                'endpoint' => '/api/v1/testimonials',
                'path' => 'data.0.name',
                'field' => 'name',
                'initial' => 'QA_TEST_TESTIMONIAL',
                'updated' => 'QA_TEST_TESTIMONIAL_UPDATED',
                'attributes' => [
                    'type' => 'written',
                    'name' => 'QA_TEST_TESTIMONIAL',
                    'program' => 'QA Program',
                    'quote' => 'QA quote',
                    'rating' => 5,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => TeamMember::class,
                'endpoint' => '/api/v1/team',
                'path' => 'data.0.name',
                'field' => 'name',
                'initial' => 'QA_TEST_TEAM',
                'updated' => 'QA_TEST_TEAM_UPDATED',
                'attributes' => [
                    'name' => 'QA_TEST_TEAM',
                    'role' => 'QA Role',
                    'bio' => 'QA bio',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
            [
                'model' => NavigationItem::class,
                'endpoint' => '/api/v1/navigation',
                'path' => 'data.header.0.label',
                'field' => 'label',
                'initial' => 'QA_TEST_NAV',
                'updated' => 'QA_TEST_NAV_UPDATED',
                'attributes' => [
                    'location' => 'header',
                    'label' => 'QA_TEST_NAV',
                    'url' => '/qa-test',
                    'target' => '_self',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ];
    }

    private function assertEndpointContains(string $endpoint, string $value, string $path): void
    {
        $this->getJson($endpoint)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath($path, $value);
    }

    private function assertEndpointMissing(string $endpoint, string $value): void
    {
        $this->getJson($endpoint)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissing([$value]);
    }
}
