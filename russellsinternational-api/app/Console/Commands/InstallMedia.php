<?php

namespace App\Console\Commands;

use App\Support\MediaStorage;
use Illuminate\Console\Command;

class InstallMedia extends Command
{
    protected $signature = 'media:install';

    protected $description = 'Link public/storage to the public disk and install seed media without overwriting uploads';

    public function handle(): int
    {
        $media = MediaStorage::forApp();

        $result = $media->ensureLinked();

        match ($result) {
            'already-linked' => $this->line(sprintf('  Link already correct: %s', $media->servedPath())),
            'relinked' => $this->info(sprintf('  Replaced stale directory with a link: %s', $media->servedPath())),
            default => $this->info(sprintf('  Linked %s -> %s', $media->servedPath(), $media->diskRoot())),
        };

        ['copied' => $copied, 'skipped' => $skipped] = $media->installSeedMedia();

        $this->line(sprintf('  Seed media: %d copied, %d already present', $copied, $skipped));

        return self::SUCCESS;
    }
}
