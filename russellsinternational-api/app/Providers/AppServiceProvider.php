<?php

namespace App\Providers;

use App\Support\MediaStorage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->ensureMediaIsServable();
    }

    /**
     * Railway rebuilds the container from the image on every deploy, so the
     * public/storage link never survives — only the volume mounted at the public
     * disk root does. Recreate it on boot when it is missing.
     *
     * Guarding on file_exists() alone is not enough: while public/storage was a
     * committed real directory the guard always passed, symlink() could never
     * replace it, and uploads stayed unreachable. ensureLinked() resolves that
     * case by rescuing the directory's contents onto the disk first. The check
     * is a pair of stat calls and short-circuits once the link is in place.
     */
    private function ensureMediaIsServable(): void
    {
        $media = MediaStorage::forApp();

        if ($this->app->runningUnitTests() || $media->isLinked()) {
            return;
        }

        try {
            $media->ensureLinked();
            $media->installSeedMedia();
        } catch (Throwable $e) {
            // A broken link must degrade to missing images, never to a 500.
            Log::warning('Unable to link public/storage to the public disk.', [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
