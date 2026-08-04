<?php

namespace Tests\Feature;

use App\Support\MediaStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Guards the media pipeline against the read/write path split that broke every
 * admin thumbnail and made new uploads unreachable over HTTP: uploads landed in
 * storage/app/public while the web server served public/storage, which was a
 * committed real directory instead of a link to the disk root.
 */
class MediaStorageTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = sys_get_temp_dir().'/media-storage-'.Str::random(12);
        mkdir($this->sandbox.'/public', 0o755, true);
        mkdir($this->sandbox.'/disk', 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->deleteSandbox($this->sandbox);

        parent::tearDown();
    }

    private function media(?string $seedSource = null): MediaStorage
    {
        return new MediaStorage($this->sandbox.'/public/storage', $this->sandbox.'/disk', $seedSource);
    }

    private function deleteSandbox(string $path): void
    {
        if (! file_exists($path) && ! is_link($path)) {
            return;
        }

        // Unlink the junction/symlink before recursing so the target survives.
        foreach (['/public/storage'] as $link) {
            if (is_link($path.$link)) {
                @unlink($path.$link);
            } elseif (is_dir($path.$link) && realpath($path.$link) === realpath($path.'/disk')) {
                PHP_OS_FAMILY === 'Windows' ? @rmdir($path.$link) : @unlink($path.$link);
            }
        }

        exec(sprintf(
            PHP_OS_FAMILY === 'Windows' ? 'rmdir /S /Q %s' : 'rm -rf %s',
            escapeshellarg(str_replace('/', DIRECTORY_SEPARATOR, $path))
        ));
    }

    public function test_a_file_written_to_the_disk_root_is_readable_through_the_served_path(): void
    {
        $media = $this->media();
        $media->ensureLinked();

        $path = Str::random(12).'.txt';
        file_put_contents($media->diskRoot().'/'.$path, 'probe');

        $this->assertFileExists(
            $media->servedPath().'/'.$path,
            'An upload written to the "public" disk must be readable under public/storage, '
            .'otherwise its /storage/... URL returns 404 and the upload never appears.'
        );
    }

    public function test_a_file_placed_in_the_served_path_is_visible_on_the_disk_root(): void
    {
        $media = $this->media();
        $media->ensureLinked();

        $path = Str::random(12).'.txt';
        file_put_contents($media->servedPath().'/'.$path, 'probe');

        $this->assertFileExists(
            $media->diskRoot().'/'.$path,
            'Files reachable at /storage/... must also exist on the "public" disk, otherwise '
            .'Filament ImageColumn::getImageUrl() fails its exists() check and renders a broken thumbnail.'
        );
    }

    public function test_a_legacy_committed_directory_is_replaced_by_a_link_without_losing_files(): void
    {
        $served = $this->sandbox.'/public/storage';
        mkdir($served.'/hero-slides', 0o755, true);
        file_put_contents($served.'/hero-slides/legacy.jpg', 'legacy-bytes');

        $media = $this->media();
        $this->assertFalse($media->isLinked(), 'A committed real directory must not count as linked.');

        $this->assertSame('relinked', $media->ensureLinked());
        $this->assertTrue($media->isLinked());

        $this->assertSame(
            'legacy-bytes',
            file_get_contents($media->diskRoot().'/hero-slides/legacy.jpg'),
            'Files from the legacy directory must be rescued onto the disk, not deleted.'
        );
    }

    public function test_ensure_linked_is_idempotent(): void
    {
        $media = $this->media();

        $this->assertSame('linked', $media->ensureLinked());
        $this->assertSame('already-linked', $media->ensureLinked());
        $this->assertSame('already-linked', $media->ensureLinked());
    }

    public function test_seed_media_is_installed_but_never_overwrites_an_upload(): void
    {
        $seed = $this->sandbox.'/seed';
        mkdir($seed.'/hero-slides', 0o755, true);
        file_put_contents($seed.'/hero-slides/seeded.jpg', 'seed-bytes');
        file_put_contents($seed.'/hero-slides/collide.jpg', 'seed-bytes');

        $media = $this->media($seed);
        $media->ensureLinked();

        // An upload that shares a seed filename must win.
        mkdir($media->diskRoot().'/hero-slides', 0o755, true);
        file_put_contents($media->diskRoot().'/hero-slides/collide.jpg', 'upload-bytes');

        $result = $media->installSeedMedia();

        $this->assertSame(['copied' => 1, 'skipped' => 1], $result);
        $this->assertSame('seed-bytes', file_get_contents($media->diskRoot().'/hero-slides/seeded.jpg'));
        $this->assertSame(
            'upload-bytes',
            file_get_contents($media->diskRoot().'/hero-slides/collide.jpg'),
            'Seed media must never overwrite a real upload.'
        );
    }

    public function test_livewire_scratch_files_are_not_installed_as_seed_media(): void
    {
        $seed = $this->sandbox.'/seed';
        mkdir($seed.'/livewire-tmp', 0o755, true);
        file_put_contents($seed.'/livewire-tmp/scratch.tmp', 'junk');

        $media = $this->media($seed);
        $media->ensureLinked();

        $this->assertSame(['copied' => 0, 'skipped' => 0], $media->installSeedMedia());
        $this->assertFileDoesNotExist($media->diskRoot().'/livewire-tmp/scratch.tmp');
    }

    public function test_the_running_application_serves_uploads_at_the_url_it_advertises(): void
    {
        $path = 'media-contract/'.Str::random(12).'.txt';

        Storage::disk('public')->put($path, 'probe');

        try {
            $this->assertFileExists(
                public_path('storage/'.$path),
                'This environment is not linked. Run: php artisan media:install'
            );

            $this->assertSame(
                rtrim(config('app.url'), '/').'/storage/'.$path,
                Storage::disk('public')->url($path)
            );
        } finally {
            Storage::disk('public')->deleteDirectory('media-contract');
        }
    }
}
