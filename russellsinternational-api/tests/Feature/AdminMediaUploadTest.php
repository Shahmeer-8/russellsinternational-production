<?php

namespace Tests\Feature;

use App\Filament\Resources\EventResource\Pages\CreateEvent;
use App\Filament\Resources\GalleryPhotoResource\Pages\CreateGalleryPhoto;
use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use App\Filament\Resources\HeroSlideResource\Pages\EditHeroSlide;
use App\Filament\Resources\StudyDestinationResource\Pages\CreateStudyDestination;
use App\Filament\Resources\TeamMemberResource\Pages\CreateTeamMember;
use App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\HeroSlide;
use App\Models\StudyDestination;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * An upload is only "done" when the saved path is reachable at the URL the app
 * advertises. Asserting the database column alone is what let the original bug
 * hide: rows updated correctly while every file 404'd, because uploads were
 * written to the public disk and requests were served from public/storage.
 *
 * These tests write to the real public disk on purpose — a faked disk cannot
 * observe the link between the two paths — and clean up after themselves.
 */
class AdminMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $uploadedPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->uploadedPaths as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->uploadedPaths = [];

        parent::tearDown();
    }

    private function loginAsAdmin(): void
    {
        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-upload-admin@example.com',
            'password' => 'password',
        ]));
    }

    /**
     * Self-contained fixture: the CLI PHP build has no GD, so
     * UploadedFile::fake()->image() cannot generate one.
     */
    private function jpeg(string $name = 'probe.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));
    }

    private function assertUploadIsServable(string $path): void
    {
        $this->uploadedPaths[] = $path;

        $this->assertTrue(
            Storage::disk('public')->exists($path),
            "Uploaded file [{$path}] is missing from the public disk, so Filament renders a broken thumbnail."
        );

        $this->assertFileExists(
            public_path('storage/'.$path),
            "Uploaded file [{$path}] is not reachable under public/storage, so its /storage/... URL returns 404."
        );

        $this->assertSame(
            rtrim(config('app.url'), '/').'/storage/'.$path,
            Storage::disk('public')->url($path)
        );
    }

    /**
     * @return array<string, array{0: class-string, 1: class-string<Model>, 2: array<string, mixed>, 3: string}>
     */
    public static function uploadResourceProvider(): array
    {
        return [
            'Hero slide' => [
                CreateHeroSlide::class, HeroSlide::class,
                [
                    'eyebrow' => 'QA_UPLOAD Eyebrow',
                    'title' => 'QA_UPLOAD Title',
                    'description' => 'QA upload description.',
                    'cta_label' => 'Explore',
                    'cta_url' => '/skills',
                ],
                'hero-slides',
            ],
            'Gallery photo' => [
                CreateGalleryPhoto::class, GalleryPhoto::class,
                [
                    'alt_text' => 'QA_UPLOAD Gallery',
                    'category' => 'Campus',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                'gallery',
            ],
            'Event' => [
                CreateEvent::class, Event::class,
                [
                    'content_type' => 'event',
                    'tag' => 'QA',
                    'tag_color' => 'bg-blue-50 text-blue-700',
                    'title' => 'QA_UPLOAD Event',
                    'event_date' => '2027-01-01',
                    'short_description' => 'QA upload event description.',
                    'is_featured' => false,
                    'is_active' => true,
                ],
                'events',
            ],
            'Study destination' => [
                CreateStudyDestination::class, StudyDestination::class,
                [
                    'flag_emoji' => 'QA',
                    'country' => 'QA_UPLOAD Destination',
                    'partner_unis_count' => '1+',
                    'highlight_unis' => 'QA Uni',
                    'intake_periods' => 'Jan',
                    'visa_success_rate' => '90%',
                    'description' => 'QA upload destination description.',
                    'services' => [['item' => 'QA service']],
                    'scholarships' => [['item' => 'QA scholarship']],
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                'destinations',
            ],
            'Team member' => [
                CreateTeamMember::class, TeamMember::class,
                [
                    'name' => 'QA_UPLOAD Member',
                    'role' => 'QA Role',
                    'bio' => 'QA bio',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                'team',
            ],
            'Testimonial' => [
                CreateTestimonial::class, Testimonial::class,
                [
                    'type' => 'written',
                    'name' => 'QA_UPLOAD Author',
                    'program' => 'QA Program',
                    'quote' => 'QA upload quote.',
                    'rating' => 5,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                'testimonials',
            ],
        ];
    }

    /**
     * @param  class-string  $createPage
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $data
     */
    #[DataProvider('uploadResourceProvider')]
    public function test_an_image_uploaded_through_the_admin_form_is_servable(
        string $createPage,
        string $model,
        array $data,
        string $directory,
    ): void {
        $this->loginAsAdmin();

        Livewire::test($createPage)
            ->fillForm($data + ['image' => $this->jpeg()])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = $model::query()->latest('id')->firstOrFail();

        $this->assertNotEmpty($record->image, 'The upload did not persist a path on the record.');
        $this->assertStringStartsWith($directory.'/', $record->image);

        $this->assertUploadIsServable($record->image);
    }

    /**
     * Replacing an image on an existing record is not covered here: fillForm()
     * on an edit form regenerates the FileUpload's UUID key but keeps the stored
     * path, discarding the UploadedFile, because the real flow goes through
     * FilePond's temporary upload endpoint. That path is verified in the browser
     * instead — see the QA report.
     */
    public function test_editing_a_record_without_touching_the_image_keeps_it_servable(): void
    {
        $this->loginAsAdmin();

        Livewire::test(CreateHeroSlide::class)
            ->fillForm([
                'eyebrow' => 'QA_KEEP Eyebrow',
                'title' => 'QA_KEEP Title',
                'description' => 'QA keep description.',
                'cta_label' => 'Explore',
                'cta_url' => '/skills',
                'image' => $this->jpeg(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = HeroSlide::query()->latest('id')->firstOrFail();
        $original = $record->image;

        // Regression guard for image being required on edit, which used to force
        // a re-upload for any unrelated text change.
        Livewire::test(EditHeroSlide::class, ['record' => $record->getKey()])
            ->fillForm(['title' => 'QA_KEEP Title Updated'])
            ->call('save')
            ->assertHasNoFormErrors();

        $record->refresh();

        $this->assertSame('QA_KEEP Title Updated', $record->title);
        $this->assertSame($original, $record->image, 'An unrelated edit must not drop the image.');
        $this->assertUploadIsServable($record->image);
    }
}
