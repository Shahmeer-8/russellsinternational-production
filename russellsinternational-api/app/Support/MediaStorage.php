<?php

namespace App\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Keeps the media read path and write path pointing at the same directory.
 *
 * Uploads are written to the "public" disk (storage/app/public), which is the
 * path Railway mounts its persistent volume on. HTTP requests for /storage/...
 * are served from public/storage. Those two only agree when public/storage is a
 * symlink to the disk root — if it is a real directory, uploads land somewhere
 * the web server never looks and Filament's ImageColumn existence check fails,
 * so every thumbnail renders broken.
 *
 * Seed media therefore ships in the repository at database/seed-media and is
 * copied into the disk root on demand, rather than being committed straight
 * into public/storage where it would block the symlink.
 */
class MediaStorage
{
    public function __construct(
        private readonly string $servedPath,
        private readonly string $diskRoot,
        private readonly ?string $seedSource = null,
    ) {}

    public static function forApp(): self
    {
        return new self(
            public_path('storage'),
            storage_path('app/public'),
            base_path('database/seed-media'),
        );
    }

    public function servedPath(): string
    {
        return $this->servedPath;
    }

    public function diskRoot(): string
    {
        return $this->diskRoot;
    }

    /**
     * True when /storage/... URLs resolve to the disk uploads are written to.
     */
    public function isLinked(): bool
    {
        $served = realpath($this->servedPath);
        $root = realpath($this->diskRoot);

        // A Windows directory junction reports as a directory rather than a
        // link, so compare resolved paths instead of trusting is_link().
        return $served !== false && $root !== false && $served === $root;
    }

    /**
     * Point the served path at the public disk root, preserving any files that
     * a previous real-directory setup left behind.
     *
     * @return 'already-linked'|'linked'|'relinked'
     */
    public function ensureLinked(): string
    {
        if (! is_dir($this->diskRoot)
            && ! mkdir($this->diskRoot, 0o755, true)
            && ! is_dir($this->diskRoot)) {
            throw new RuntimeException("Unable to create the public disk root at [{$this->diskRoot}].");
        }

        if ($this->isLinked()) {
            return 'already-linked';
        }

        $existed = file_exists($this->servedPath) || is_link($this->servedPath);

        if ($existed) {
            if (is_link($this->servedPath)) {
                // Stale link aimed somewhere else.
                @unlink($this->servedPath);
            } else {
                // The legacy committed directory: rescue its contents first so
                // nothing that used to be served disappears.
                $this->copyMissing($this->servedPath, $this->diskRoot);
                $this->deleteTree($this->servedPath);
            }
        }

        $this->createLink();

        return $existed ? 'relinked' : 'linked';
    }

    /**
     * Copy repository seed media into the public disk without ever overwriting
     * a file that is already there — uploads always win over seeds.
     *
     * @return array{copied: int, skipped: int}
     */
    public function installSeedMedia(): array
    {
        if ($this->seedSource === null || ! is_dir($this->seedSource)) {
            return ['copied' => 0, 'skipped' => 0];
        }

        return $this->copyMissing($this->seedSource, $this->diskRoot);
    }

    /**
     * @return array{copied: int, skipped: int}
     */
    private function copyMissing(string $from, string $to): array
    {
        $copied = 0;
        $skipped = 0;

        if (! is_dir($from)) {
            return ['copied' => $copied, 'skipped' => $skipped];
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            /** @var SplFileInfo $item */
            $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($from) + 1));

            // Transient Livewire upload scratch space is never worth copying.
            if (str_starts_with($relative, 'livewire-tmp/') || $relative === 'livewire-tmp') {
                continue;
            }

            $target = $to.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0o755, true);
                }

                continue;
            }

            if (file_exists($target)) {
                $skipped++;

                continue;
            }

            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0o755, true);
            }

            copy($item->getPathname(), $target);
            $copied++;
        }

        return ['copied' => $copied, 'skipped' => $skipped];
    }

    private function createLink(): void
    {
        if (! is_dir(dirname($this->servedPath))) {
            mkdir(dirname($this->servedPath), 0o755, true);
        }

        if (@symlink($this->diskRoot, $this->servedPath) && $this->isLinked()) {
            return;
        }

        // Creating a symlink on Windows needs elevation or developer mode, but
        // a directory junction does not and behaves the same way for reads.
        if (PHP_OS_FAMILY === 'Windows') {
            exec(
                sprintf('mklink /J %s %s', escapeshellarg($this->servedPath), escapeshellarg($this->diskRoot)),
                $output,
                $status
            );

            if ($status === 0 && $this->isLinked()) {
                return;
            }
        }

        throw new RuntimeException(
            "Unable to link [{$this->servedPath}] to [{$this->diskRoot}]. On Windows, enable Developer Mode "
            .'or run the command from an elevated shell.'
        );
    }

    private function deleteTree(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            @unlink($path);

            return;
        }

        if (! is_dir($path)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            /** @var SplFileInfo $item */
            if ($item->isDir() && ! $item->isLink()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }
}
