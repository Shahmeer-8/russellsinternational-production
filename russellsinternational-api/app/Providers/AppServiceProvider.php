<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

        $this->ensureStorageLinkExists();
    }

    /**
     * Railway containers are rebuilt from the image on every deploy/restart, so the
     * public/storage symlink (created by `storage:link`) never survives — only the
     * mounted volume at storage/app/public does. Recreate it on boot if missing.
     */
    private function ensureStorageLinkExists(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link) && is_dir($target)) {
            @symlink($target, $link);
        }
    }
}
