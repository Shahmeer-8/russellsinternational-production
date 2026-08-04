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
     * The container's public/ directory is rebuilt on every deploy while media
     * lives on a persistent volume mounted at the public disk root, so the
     * public/storage link has to be re-established at runtime. The check is a
     * pair of stat calls and short-circuits once the link is in place.
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
