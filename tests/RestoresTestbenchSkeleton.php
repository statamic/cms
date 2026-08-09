<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Foundation\Config;

use function Orchestra\Testbench\default_skeleton_path;

trait RestoresTestbenchSkeleton
{
    use DeletesDirectories;

    /**
     * The set of paths, relative to the skeleton root, that existed before any test in this
     * process had a chance to touch it. Paths are keys so lookups are hash based.
     */
    private static ?array $skeletonSnapshot = null;

    private static ?string $skeletonPath = null;

    private static ?string $skeletonRealPath = null;

    /**
     * Subtrees the framework owns and rebuilds on demand. Deleting these breaks
     * subsequent tests ("Please provide a valid cache path"), and walking them gets
     * expensive once thousands of compiled views have piled up.
     */
    private static array $skeletonExclusions = [
        'bootstrap/cache',
        'node_modules',
        'storage/framework/cache',
        'storage/framework/views',
        'vendor',
    ];

    /**
     * Runs once per process, before the first app is booted. Booting first would mean the
     * directories the boot creates - a disk's root, say - were already there when the
     * snapshot was taken, making them part of the baseline and invisible to the restore for
     * the rest of the process.
     */
    protected function prepareTestbenchSkeleton(): void
    {
        if (self::$skeletonSnapshot !== null) {
            return;
        }

        self::$skeletonPath = default_skeleton_path();
        self::$skeletonRealPath = realpath(self::$skeletonPath) ?: self::$skeletonPath;

        $this->purgeTestbenchSkeleton();

        self::$skeletonSnapshot = $this->scanTestbenchSkeleton();
    }

    /**
     * The snapshot only stops tests within a process from leaking into each other. A process
     * starting against a skeleton dirtied by an earlier run would bake that dirt into its
     * snapshot, so clear the known offenders first.
     */
    private function purgeTestbenchSkeleton(): void
    {
        $files = new Filesystem;

        $purge = Config::loadFromYaml(__DIR__.'/..')->getPurgeAttributes();

        $expand = fn ($paths) => (new Collection($paths))
            ->map(fn ($path) => default_skeleton_path().'/'.$path)
            ->flatMap(fn ($path) => str_contains($path, '*') ? $files->glob($path) : [$path]);

        foreach ($expand($purge['files']) as $file) {
            $files->delete($file);
        }

        foreach ($expand($purge['directories']) as $directory) {
            $this->deleteDirectory($directory);
        }
    }

    protected function restoreTestbenchSkeleton(): void
    {
        if (self::$skeletonSnapshot === null) {
            return;
        }

        $added = array_diff_key($this->scanTestbenchSkeleton(), self::$skeletonSnapshot);

        // Deepest first, so directories are empty by the time we get to them.
        uksort($added, fn ($a, $b) => substr_count($b, '/') <=> substr_count($a, '/'));

        foreach ($added as $path => $ignored) {
            $absolute = self::$skeletonPath.'/'.$path;

            // Same reasoning as DeletesDirectories: never ask whether the path is a file, a
            // directory or a link, because a junction gives contradictory answers. unlink()
            // takes files and file links, rmdir() takes empty directories, directory links
            // and junctions without following them.
            @unlink($absolute) || @rmdir($absolute);
        }
    }

    private function scanTestbenchSkeleton(): array
    {
        $paths = [];

        $scan = function ($relative) use (&$scan, &$paths) {
            $absolute = self::$skeletonPath.($relative ? '/'.$relative : '');

            foreach (scandir($absolute) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $path = $relative ? $relative.'/'.$entry : $entry;

                if (in_array($path, self::$skeletonExclusions)) {
                    continue;
                }

                if ($this->isRealSkeletonDirectory($absolute.'/'.$entry)) {
                    $scan($path);
                }

                $paths[$path] = true;
            }
        };

        $scan('');

        return $paths;
    }

    /**
     * Whether the scan may descend into a path. Recursing through a link would record its
     * target's contents as skeleton paths, and the restore would then delete files that can
     * live anywhere on disk, so anything we can't positively place inside the skeleton is
     * left alone. Resolving the path is what settles it: a junction whose lstat mode makes
     * is_dir() and is_link() disagree still resolves to wherever it points.
     */
    private function isRealSkeletonDirectory(string $path): bool
    {
        clearstatcache(true, $path);

        if (is_link($path) || ! is_dir($path)) {
            return false;
        }

        $resolved = realpath($path);

        return $resolved !== false
            && str_starts_with($resolved.DIRECTORY_SEPARATOR, self::$skeletonRealPath.DIRECTORY_SEPARATOR);
    }
}
