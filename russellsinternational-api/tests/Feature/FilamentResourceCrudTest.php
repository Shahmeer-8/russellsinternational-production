<?php

namespace Tests\Feature;

use App\Filament\Resources\CareerApplicationResource\Pages\EditCareerApplication;
use App\Filament\Resources\CareerApplicationResource\Pages\ListCareerApplications;
use App\Filament\Resources\ContactSubmissionResource\Pages\EditContactSubmission;
use App\Filament\Resources\ContactSubmissionResource\Pages\ListContactSubmissions;
use App\Filament\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Resources\EventResource\Pages\CreateEvent;
use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Filament\Resources\GalleryPhotoResource\Pages\CreateGalleryPhoto;
use App\Filament\Resources\GalleryPhotoResource\Pages\EditGalleryPhoto;
use App\Filament\Resources\GalleryPhotoResource\Pages\ListGalleryPhotos;
use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use App\Filament\Resources\HeroSlideResource\Pages\EditHeroSlide;
use App\Filament\Resources\HeroSlideResource\Pages\ListHeroSlides;
use App\Filament\Resources\InternshipResource\Pages\CreateInternship;
use App\Filament\Resources\InternshipResource\Pages\EditInternship;
use App\Filament\Resources\InternshipResource\Pages\ListInternships;
use App\Filament\Resources\JobResource\Pages\CreateJob;
use App\Filament\Resources\JobResource\Pages\EditJob;
use App\Filament\Resources\JobResource\Pages\ListJobs;
use App\Filament\Resources\LanguageProgramResource\Pages\CreateLanguageProgram;
use App\Filament\Resources\LanguageProgramResource\Pages\EditLanguageProgram;
use App\Filament\Resources\LanguageProgramResource\Pages\ListLanguagePrograms;
use App\Filament\Resources\NavigationItemResource\Pages\CreateNavigationItem;
use App\Filament\Resources\NavigationItemResource\Pages\EditNavigationItem;
use App\Filament\Resources\NavigationItemResource\Pages\ListNavigationItems;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Filament\Resources\PageSectionResource\Pages\CreatePageSection;
use App\Filament\Resources\PageSectionResource\Pages\EditPageSection;
use App\Filament\Resources\PageSectionResource\Pages\ListPageSections;
use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Filament\Resources\SettingResource\Pages\CreateSetting;
use App\Filament\Resources\SettingResource\Pages\EditSetting;
use App\Filament\Resources\SettingResource\Pages\ListSettings;
use App\Filament\Resources\StatResource\Pages\CreateStat;
use App\Filament\Resources\StatResource\Pages\EditStat;
use App\Filament\Resources\StatResource\Pages\ListStats;
use App\Filament\Resources\StudyDestinationResource\Pages\CreateStudyDestination;
use App\Filament\Resources\StudyDestinationResource\Pages\EditStudyDestination;
use App\Filament\Resources\StudyDestinationResource\Pages\ListStudyDestinations;
use App\Filament\Resources\TeamMemberResource\Pages\CreateTeamMember;
use App\Filament\Resources\TeamMemberResource\Pages\EditTeamMember;
use App\Filament\Resources\TeamMemberResource\Pages\ListTeamMembers;
use App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Filament\Resources\TickerItemResource\Pages\CreateTickerItem;
use App\Filament\Resources\TickerItemResource\Pages\EditTickerItem;
use App\Filament\Resources\TickerItemResource\Pages\ListTickerItems;
use App\Filament\Resources\WhyChooseUsItemResource\Pages\CreateWhyChooseUsItem;
use App\Filament\Resources\WhyChooseUsItemResource\Pages\EditWhyChooseUsItem;
use App\Filament\Resources\WhyChooseUsItemResource\Pages\ListWhyChooseUsItems;
use App\Models\CareerApplication;
use App\Models\ContactSubmission;
use App\Models\Course;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\HeroSlide;
use App\Models\Internship;
use App\Models\Job;
use App\Models\LanguageProgram;
use App\Models\LanguageSection;
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
use App\Models\User;
use App\Models\WhyChooseUsItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_high_priority_resources_crud_through_filament_livewire(): void
    {
        $this->loginAsAdmin();

        foreach ($this->resourceCases() as $case) {
            Livewire::test($case['list'])->assertSuccessful();

            Livewire::test($case['create'])
                ->fillForm($case['createData'])
                ->call('create')
                ->assertHasNoFormErrors();

            /** @var class-string<Model> $model */
            $model = $case['model'];
            $record = $model::where($case['lookupField'], $case['lookupValue'])->firstOrFail();

            Livewire::test($case['edit'], ['record' => $record->getKey()])
                ->fillForm($case['editData'])
                ->call('save')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas($record->getTable(), array_merge(['id' => $record->getKey()], $case['databaseAfterEdit']));

            $record->refresh()->delete();
            $this->assertDatabaseMissing($record->getTable(), ['id' => $record->getKey()]);
        }
    }

    public function test_sort_order_fields_reject_database_out_of_range_values(): void
    {
        $this->loginAsAdmin();

        Livewire::test(CreateCourse::class)
            ->fillForm([
                'type' => 'paid',
                'title' => 'QA_TEST_SORT_ORDER',
                'icon_name' => 'Code',
                'duration' => '1 Week',
                'students_count' => '1',
                'sort_order' => 999,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['sort_order']);
    }

    public function test_submission_resources_list_and_edit_status_through_filament_livewire(): void
    {
        $this->loginAsAdmin();

        $contact = ContactSubmission::create([
            'name' => 'QA_TEST_CONTACT_ADMIN',
            'email' => 'qa-contact@example.com',
            'status' => 'new',
        ]);

        Livewire::test(ListContactSubmissions::class)
            ->assertSuccessful();

        Livewire::test(EditContactSubmission::class, ['record' => $contact->getKey()])
            ->fillForm(['status' => 'replied', 'admin_notes' => 'QA updated'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contact_submissions', ['id' => $contact->id, 'status' => 'replied']);

        $application = CareerApplication::create([
            'application_type' => 'job',
            'position_title' => 'QA_TEST_APPLICATION_ADMIN',
            'name' => 'QA Applicant',
            'email' => 'qa-application@example.com',
            'status' => 'new',
        ]);

        Livewire::test(ListCareerApplications::class)
            ->assertSuccessful();

        Livewire::test(EditCareerApplication::class, ['record' => $application->getKey()])
            ->fillForm(['status' => 'shortlisted', 'admin_notes' => 'QA updated'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('career_applications', ['id' => $application->id, 'status' => 'shortlisted']);
    }

    private function loginAsAdmin(): void
    {
        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-admin@example.com',
            'password' => 'password',
        ]));
    }

    /**
     * @return array<int, array{model: class-string<Model>, list: class-string, create: class-string, edit: class-string, lookupField: string, lookupValue: string, createData: array<string, mixed>, editData: array<string, mixed>, databaseAfterEdit: array<string, mixed>}>
     */
    /**
     * The language sections are seeded by migration, so their ids are looked up
     * rather than hardcoded.
     */
    private function sectionId(string $slug): ?int
    {
        return LanguageSection::query()->where('slug', $slug)->value('id');
    }

    private function resourceCases(): array
    {
        return [
            [
                'model' => Stat::class,
                'list' => ListStats::class,
                'create' => CreateStat::class,
                'edit' => EditStat::class,
                'lookupField' => 'label',
                'lookupValue' => 'QA_TEST_STAT_ADMIN',
                'createData' => ['value' => '1', 'label' => 'QA_TEST_STAT_ADMIN', 'icon_name' => 'Users', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['value' => '2', 'label' => 'QA_TEST_STAT_ADMIN_UPDATED', 'icon_name' => 'Users', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['value' => '2', 'label' => 'QA_TEST_STAT_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => TickerItem::class,
                'list' => ListTickerItems::class,
                'create' => CreateTickerItem::class,
                'edit' => EditTickerItem::class,
                'lookupField' => 'text',
                'lookupValue' => 'QA_TEST_TICKER_ADMIN',
                'createData' => ['emoji' => 'QA', 'text' => 'QA_TEST_TICKER_ADMIN', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['emoji' => 'QA', 'text' => 'QA_TEST_TICKER_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['text' => 'QA_TEST_TICKER_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => WhyChooseUsItem::class,
                'list' => ListWhyChooseUsItems::class,
                'create' => CreateWhyChooseUsItem::class,
                'edit' => EditWhyChooseUsItem::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_WHY_ADMIN',
                'createData' => ['icon_name' => 'Shield', 'title' => 'QA_TEST_WHY_ADMIN', 'description' => 'QA description', 'color_class' => 'bg-blue-50 text-blue-600', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['icon_name' => 'Shield', 'title' => 'QA_TEST_WHY_ADMIN_UPDATED', 'description' => 'QA updated', 'color_class' => 'bg-blue-50 text-blue-600', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_WHY_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => Service::class,
                'list' => ListServices::class,
                'create' => CreateService::class,
                'edit' => EditService::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_SERVICE_ADMIN',
                'createData' => ['icon_name' => 'Code', 'title' => 'QA_TEST_SERVICE_ADMIN', 'description' => 'QA description', 'details' => 'QA details', 'key_benefits' => [['item' => 'QA benefit']], 'color_class' => 'bg-blue-50 text-blue-600', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['icon_name' => 'Code', 'title' => 'QA_TEST_SERVICE_ADMIN_UPDATED', 'description' => 'QA updated', 'details' => 'QA details updated', 'key_benefits' => [['item' => 'QA benefit updated']], 'color_class' => 'bg-blue-50 text-blue-600', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_SERVICE_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => Course::class,
                'list' => ListCourses::class,
                'create' => CreateCourse::class,
                'edit' => EditCourse::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_COURSE_ADMIN',
                'createData' => ['type' => 'paid', 'title' => 'QA_TEST_COURSE_ADMIN', 'icon_name' => 'Code', 'description' => 'QA description', 'duration' => '1 Week', 'students_count' => '1', 'what_you_learn' => [['item' => 'QA learn']], 'highlights' => [['item' => 'QA highlight']], 'sort_order' => 1, 'is_active' => true],
                'editData' => ['type' => 'paid', 'title' => 'QA_TEST_COURSE_ADMIN_UPDATED', 'icon_name' => 'Code', 'description' => 'QA updated', 'duration' => '2 Weeks', 'students_count' => '2', 'what_you_learn' => [['item' => 'QA learn updated']], 'highlights' => [['item' => 'QA highlight updated']], 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_COURSE_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => Job::class,
                'list' => ListJobs::class,
                'create' => CreateJob::class,
                'edit' => EditJob::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_JOB_ADMIN',
                'createData' => ['title' => 'QA_TEST_JOB_ADMIN', 'company' => 'QA Company', 'location' => 'Remote', 'type' => 'Full-Time', 'description' => 'QA description', 'requirements' => [['item' => 'QA requirement']], 'is_active' => true],
                'editData' => ['title' => 'QA_TEST_JOB_ADMIN_UPDATED', 'company' => 'QA Company', 'location' => 'Remote', 'type' => 'Part-Time', 'description' => 'QA updated', 'requirements' => [['item' => 'QA requirement updated']], 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_JOB_ADMIN_UPDATED', 'type' => 'Part-Time', 'is_active' => false],
            ],
            [
                'model' => Internship::class,
                'list' => ListInternships::class,
                'create' => CreateInternship::class,
                'edit' => EditInternship::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_INTERNSHIP_ADMIN',
                'createData' => ['title' => 'QA_TEST_INTERNSHIP_ADMIN', 'company' => 'QA Company', 'location' => 'Remote', 'duration' => '1 Month', 'type' => 'Remote', 'description' => 'QA description', 'skills' => [['item' => 'QA skill']], 'gains' => [['item' => 'QA gain']], 'is_active' => true],
                'editData' => ['title' => 'QA_TEST_INTERNSHIP_ADMIN_UPDATED', 'company' => 'QA Company', 'location' => 'Remote', 'duration' => '2 Months', 'type' => 'Hybrid', 'description' => 'QA updated', 'skills' => [['item' => 'QA skill updated']], 'gains' => [['item' => 'QA gain updated']], 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_INTERNSHIP_ADMIN_UPDATED', 'duration' => '2 Months', 'is_active' => false],
            ],
            [
                'model' => Event::class,
                'list' => ListEvents::class,
                'create' => CreateEvent::class,
                'edit' => EditEvent::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_EVENT_ADMIN',
                'createData' => ['content_type' => 'event', 'tag' => 'QA', 'tag_color' => 'bg-blue-50 text-blue-700', 'title' => 'QA_TEST_EVENT_ADMIN', 'event_date' => now()->addDay(), 'short_description' => 'QA description', 'is_featured' => false, 'is_active' => true],
                'editData' => ['content_type' => 'news', 'tag' => 'QA', 'tag_color' => 'bg-blue-50 text-blue-700', 'title' => 'QA_TEST_EVENT_ADMIN_UPDATED', 'event_date' => now()->addDays(2), 'short_description' => 'QA updated', 'is_featured' => true, 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_EVENT_ADMIN_UPDATED', 'content_type' => 'news', 'is_featured' => true, 'is_active' => false],
            ],
            [
                'model' => GalleryPhoto::class,
                'list' => ListGalleryPhotos::class,
                'create' => CreateGalleryPhoto::class,
                'edit' => EditGalleryPhoto::class,
                'lookupField' => 'alt_text',
                'lookupValue' => 'QA_TEST_GALLERY_ADMIN',
                'createData' => ['image' => $this->fakeImage('qa-gallery.png'), 'alt_text' => 'QA_TEST_GALLERY_ADMIN', 'category' => 'Campus', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['alt_text' => 'QA_TEST_GALLERY_ADMIN_UPDATED', 'category' => 'Events', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['alt_text' => 'QA_TEST_GALLERY_ADMIN_UPDATED', 'category' => 'Events', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => HeroSlide::class,
                'list' => ListHeroSlides::class,
                'create' => CreateHeroSlide::class,
                'edit' => EditHeroSlide::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_HERO_ADMIN',
                'createData' => ['eyebrow' => 'QA', 'title' => 'QA_TEST_HERO_ADMIN', 'description' => 'QA description', 'cta_label' => 'Explore', 'cta_url' => '/skills', 'secondary_cta_label' => 'Contact', 'secondary_cta_url' => '/#contact', 'image' => $this->fakeImage('qa-hero.png'), 'sort_order' => 1, 'is_active' => true],
                'editData' => ['eyebrow' => 'QA', 'title' => 'QA_TEST_HERO_ADMIN_UPDATED', 'description' => 'QA updated', 'cta_label' => 'Explore', 'cta_url' => '/skills', 'secondary_cta_label' => 'Contact', 'secondary_cta_url' => '/#contact', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_HERO_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => LanguageProgram::class,
                'list' => ListLanguagePrograms::class,
                'create' => CreateLanguageProgram::class,
                'edit' => EditLanguageProgram::class,
                'lookupField' => 'title',
                'lookupValue' => 'QA_TEST_LANGUAGE_ADMIN',
                'createData' => ['flag_emoji' => 'QA', 'language_section_id' => $this->sectionId('english'), 'title' => 'QA_TEST_LANGUAGE_ADMIN', 'duration' => '1 Week', 'badge' => 'QA', 'color_class' => 'bg-blue-50 text-blue-600', 'description' => 'QA description', 'benefits' => [['item' => 'QA benefit']], 'sort_order' => 1, 'is_active' => true],
                'editData' => ['flag_emoji' => 'QA', 'language_section_id' => $this->sectionId('german'), 'title' => 'QA_TEST_LANGUAGE_ADMIN_UPDATED', 'duration' => '2 Weeks', 'badge' => 'QA2', 'color_class' => 'bg-blue-50 text-blue-600', 'description' => 'QA updated', 'benefits' => [['item' => 'QA benefit updated']], 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['title' => 'QA_TEST_LANGUAGE_ADMIN_UPDATED', 'language_section_id' => $this->sectionId('german'), 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => StudyDestination::class,
                'list' => ListStudyDestinations::class,
                'create' => CreateStudyDestination::class,
                'edit' => EditStudyDestination::class,
                'lookupField' => 'country',
                'lookupValue' => 'QA_TEST_COUNTRY_ADMIN',
                'createData' => ['flag_emoji' => 'QA', 'country' => 'QA_TEST_COUNTRY_ADMIN', 'partner_unis_count' => '1+', 'highlight_unis' => 'QA Uni', 'intake_periods' => 'Jan', 'visa_success_rate' => '90%', 'description' => 'QA description', 'services' => [['item' => 'QA service']], 'scholarships' => [['item' => 'QA scholarship']], 'sort_order' => 1, 'is_active' => true],
                'editData' => ['flag_emoji' => 'QA', 'country' => 'QA_TEST_COUNTRY_ADMIN_UPDATED', 'partner_unis_count' => '2+', 'highlight_unis' => 'QA Uni 2', 'intake_periods' => 'Sep', 'visa_success_rate' => '95%', 'description' => 'QA updated', 'services' => [['item' => 'QA service updated']], 'scholarships' => [['item' => 'QA scholarship updated']], 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['country' => 'QA_TEST_COUNTRY_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => TeamMember::class,
                'list' => ListTeamMembers::class,
                'create' => CreateTeamMember::class,
                'edit' => EditTeamMember::class,
                'lookupField' => 'name',
                'lookupValue' => 'QA_TEST_TEAM_ADMIN',
                'createData' => ['name' => 'QA_TEST_TEAM_ADMIN', 'role' => 'QA Role', 'bio' => 'QA bio', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['name' => 'QA_TEST_TEAM_ADMIN_UPDATED', 'role' => 'QA Role 2', 'bio' => 'QA updated', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['name' => 'QA_TEST_TEAM_ADMIN_UPDATED', 'role' => 'QA Role 2', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => Testimonial::class,
                'list' => ListTestimonials::class,
                'create' => CreateTestimonial::class,
                'edit' => EditTestimonial::class,
                'lookupField' => 'name',
                'lookupValue' => 'QA_TEST_TESTIMONIAL_ADMIN',
                'createData' => ['type' => 'written', 'name' => 'QA_TEST_TESTIMONIAL_ADMIN', 'program' => 'QA Program', 'quote' => 'QA quote', 'rating' => 5, 'sort_order' => 1, 'is_active' => true],
                'editData' => ['type' => 'written', 'name' => 'QA_TEST_TESTIMONIAL_ADMIN_UPDATED', 'program' => 'QA Program 2', 'quote' => 'QA updated', 'rating' => 4, 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['name' => 'QA_TEST_TESTIMONIAL_ADMIN_UPDATED', 'rating' => 4, 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => NavigationItem::class,
                'list' => ListNavigationItems::class,
                'create' => CreateNavigationItem::class,
                'edit' => EditNavigationItem::class,
                'lookupField' => 'label',
                'lookupValue' => 'QA_TEST_NAV_ADMIN',
                'createData' => ['location' => 'header', 'label' => 'QA_TEST_NAV_ADMIN', 'url' => '/qa', 'target' => '_self', 'sort_order' => 1, 'is_active' => true, 'preview_approved' => true],
                'editData' => ['location' => 'footer', 'footer_column' => 'QA', 'label' => 'QA_TEST_NAV_ADMIN_UPDATED', 'url' => '/qa-updated', 'target' => '_blank', 'sort_order' => 2, 'is_active' => false, 'preview_approved' => true],
                'databaseAfterEdit' => ['label' => 'QA_TEST_NAV_ADMIN_UPDATED', 'location' => 'footer', 'is_active' => false],
            ],
            [
                'model' => Page::class,
                'list' => ListPages::class,
                'create' => CreatePage::class,
                'edit' => EditPage::class,
                'lookupField' => 'slug',
                'lookupValue' => 'qa-test-admin',
                'createData' => ['slug' => 'qa-test-admin', 'name' => 'QA_TEST_PAGE_ADMIN', 'is_active' => true],
                'editData' => ['slug' => 'qa-test-admin', 'name' => 'QA_TEST_PAGE_ADMIN_UPDATED', 'is_active' => false],
                'databaseAfterEdit' => ['name' => 'QA_TEST_PAGE_ADMIN_UPDATED', 'is_active' => false],
            ],
            [
                'model' => PageSection::class,
                'list' => ListPageSections::class,
                'create' => CreatePageSection::class,
                'edit' => EditPageSection::class,
                'lookupField' => 'name',
                'lookupValue' => 'QA_TEST_SECTION_ADMIN',
                'createData' => ['page_slug' => 'about', 'section_key' => 'qa-admin', 'name' => 'QA_TEST_SECTION_ADMIN', 'title' => 'QA section', 'sort_order' => 1, 'is_active' => true],
                'editData' => ['page_slug' => 'about', 'section_key' => 'qa-admin', 'name' => 'QA_TEST_SECTION_ADMIN_UPDATED', 'title' => 'QA updated', 'sort_order' => 2, 'is_active' => false],
                'databaseAfterEdit' => ['name' => 'QA_TEST_SECTION_ADMIN_UPDATED', 'sort_order' => 2, 'is_active' => false],
            ],
            [
                'model' => Setting::class,
                'list' => ListSettings::class,
                'create' => CreateSetting::class,
                'edit' => EditSetting::class,
                'lookupField' => 'key',
                'lookupValue' => 'qa_test_admin_setting',
                'createData' => ['key' => 'qa_test_admin_setting', 'label' => 'QA Test Setting', 'group' => 'general', 'type' => 'text', 'value' => 'QA_TEST_SETTING_ADMIN'],
                'editData' => ['key' => 'qa_test_admin_setting', 'label' => 'QA Test Setting Updated', 'group' => 'general', 'type' => 'text', 'value' => 'QA_TEST_SETTING_ADMIN_UPDATED'],
                'databaseAfterEdit' => ['label' => 'QA Test Setting Updated', 'value' => 'QA_TEST_SETTING_ADMIN_UPDATED'],
            ],
        ];
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );
    }
}
