<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Foundation\Config;

use function Orchestra\Testbench\default_skeleton_path;

trait RestoresTestbenchSkeleton
{
    /**
     * The skeleton's contents before any test in this process had a chance to touch it,
     * keyed by path relative to the skeleton root so lookups are hash based.
     */
    private static ?array $skeletonSnapshot = null;

    private static ?string $skeletonPath = null;

    private static bool $skeletonPurged = false;

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
     * The snapshot below only stops tests within a process from leaking into each other.
     * A process starting against a skeleton dirtied by an earlier run would bake that dirt
     * into its snapshot, so clear the known offenders before the app is ever booted.
     */
    protected function purgeTestbenchSkeleton(): void
    {
        if (self::$skeletonPurged) {
            return;
        }

        self::$skeletonPurged = true;

        $files = new Filesystem;

        $purge = Config::loadFromYaml(__DIR__.'/..')->getPurgeAttributes();

        $expand = fn ($paths) => (new Collection($paths))
            ->map(fn ($path) => default_skeleton_path().'/'.$path)
            ->flatMap(fn ($path) => str_contains($path, '*') ? $files->glob($path) : [$path]);

        foreach ($expand($purge['files']) as $file) {
            $files->delete($file);
        }

        foreach ($expand($purge['directories']) as $directory) {
            $files->deleteDirectory($directory);
        }
    }

    protected function snapshotTestbenchSkeleton(): void
    {
        if (self::$skeletonSnapshot !== null) {
            return;
        }

        self::$skeletonPath = $this->app->basePath();
        self::$skeletonSnapshot = $this->scanTestbenchSkeleton();
    }

    protected function restoreTestbenchSkeleton(): void
    {
        if (self::$skeletonSnapshot === null) {
            return;
        }

        $added = array_diff_key($this->scanTestbenchSkeleton(), self::$skeletonSnapshot);

        // Deepest first, so directories are empty by the time we get to them.
        uksort($added, fn ($a, $b) => substr_count($b, '/') <=> substr_count($a, '/'));

        foreach ($added as $path => $isDir) {
            $absolute = self::$skeletonPath.'/'.$path;

            $isDir ? @rmdir($absolute) : @unlink($absolute);
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

                $isDir = is_dir($absolute.'/'.$entry) && ! is_link($absolute.'/'.$entry);

                if ($isDir) {
                    $scan($path);
                }

                $paths[$path] = $isDir;
            }
        };

        $scan('');

        return $paths;
    }
}
