<?php

namespace Tests\Feature;

use App\Filament\Resources\SettingResource\Pages\CreateSetting;
use App\Filament\Resources\SettingResource\Pages\EditSetting;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * A setting stores every kind of value in one `value` column, so the form swaps
 * the input per type. Sharing one state path across a TextInput, Textarea,
 * FileUpload and Toggle meant the Toggle cast the shared state to a boolean,
 * and the FileUpload's validation rule then received `false` where it requires
 * an array — a 500 that made image settings impossible to save.
 */
class SettingValueTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $uploaded = [];

    protected function tearDown(): void
    {
        foreach ($this->uploaded as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->uploaded = [];

        parent::tearDown();
    }

    private function loginAsAdmin(): void
    {
        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-setting-admin@example.com',
            'password' => 'password',
        ]));
    }

    /**
     * Self-contained fixture: the CLI PHP build has no GD, so
     * UploadedFile::fake()->image() cannot generate one.
     */
    private function image(string $name = 'brand-logo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));
    }

    /**
     * The field name differs per type because the boolean input has its own
     * state path — see SettingResource::foldValue().
     *
     * @return array<string, array{0: string, 1: string, 2: mixed, 3: mixed}>
     */
    public static function scalarTypeProvider(): array
    {
        return [
            'text' => ['text', 'value', 'plain value', 'plain value'],
            'textarea' => ['textarea', 'value', "line one\nline two", "line one\nline two"],
            'url' => ['url', 'value', 'https://example.com/page', 'https://example.com/page'],
            'boolean true' => ['boolean', 'value_boolean', true, '1'],
            'boolean false' => ['boolean', 'value_boolean', false, '0'],
        ];
    }

    #[DataProvider('scalarTypeProvider')]
    public function test_a_setting_of_each_scalar_type_can_be_created(string $type, string $field, mixed $input, mixed $stored): void
    {
        $this->loginAsAdmin();

        Livewire::test(CreateSetting::class)
            ->fillForm([
                'key' => 'qa_'.$type.'_'.(is_bool($input) ? var_export($input, true) : 'v'),
                'label' => 'QA '.$type,
                'group' => 'general',
                'type' => $type,
                $field => $input,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $setting = Setting::query()->latest('id')->first();

        $this->assertNotNull($setting, "A [{$type}] setting was not created.");
        $this->assertSame($type, $setting->type);
        $this->assertSame($stored, $setting->value);
    }

    public function test_an_image_setting_can_be_created_and_the_file_is_servable(): void
    {
        $this->loginAsAdmin();

        $file = $this->image();

        Livewire::test(CreateSetting::class)
            ->fillForm([
                'key' => 'qa_site_logo',
                'label' => 'QA Site Logo',
                'group' => 'general',
                'type' => 'image',
                'value_image' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $setting = Setting::query()->where('key', 'qa_site_logo')->first();

        $this->assertNotNull($setting, 'An image setting could not be created.');
        $this->assertNotEmpty($setting->value, 'The uploaded path was not stored.');
        $this->assertStringStartsWith('settings/', $setting->value);

        $this->uploaded[] = $setting->value;

        $this->assertTrue(
            Storage::disk('public')->exists($setting->value),
            'The uploaded setting image is missing from the public disk.'
        );
        $this->assertFileExists(
            public_path('storage/'.$setting->value),
            'The uploaded setting image is not reachable under public/storage.'
        );
    }

    public function test_an_image_setting_keeps_its_file_when_other_fields_are_edited(): void
    {
        $this->loginAsAdmin();

        $file = $this->image();

        Livewire::test(CreateSetting::class)
            ->fillForm([
                'key' => 'qa_edit_logo',
                'label' => 'QA Edit Logo',
                'group' => 'general',
                'type' => 'image',
                'value_image' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $setting = Setting::query()->where('key', 'qa_edit_logo')->firstOrFail();
        $original = $setting->value;
        $this->uploaded[] = $original;

        Livewire::test(EditSetting::class, ['record' => $setting->getKey()])
            ->fillForm(['label' => 'QA Edit Logo Renamed'])
            ->call('save')
            ->assertHasNoFormErrors();

        $setting->refresh();

        $this->assertSame('QA Edit Logo Renamed', $setting->label);
        $this->assertSame($original, $setting->value, 'Editing an unrelated field dropped the image.');
    }

    public function test_a_boolean_setting_can_be_toggled_on_edit(): void
    {
        $this->loginAsAdmin();

        $setting = Setting::create([
            'key' => 'qa_flag',
            'label' => 'QA Flag',
            'group' => 'general',
            'type' => 'boolean',
            'value' => '0',
        ]);

        Livewire::test(EditSetting::class, ['record' => $setting->getKey()])
            ->fillForm(['value_boolean' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('1', $setting->refresh()->value);
    }
}
