<?php

namespace Statamic\Sidecar\Structures;

use Statamic\Contracts\Entries\Entry;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Sidecar;
use Statamic\Sidecar\Driver;
use Statamic\Support\Str;

/**
 * Sync a Sidecar collection structure tree to on-disk nested folders.
 *
 * On CollectionTreeSaved for drivers that opt into nested folders:
 * - rewrite each entry's path from tree ancestry (via buildPath)
 * - persist sibling `order` via the driver
 * - delete directories left empty after moves
 *
 * @experimental
 */
class SyncTreeToFilesystem
{
    public function handle(CollectionTreeSaved $event): void
    {
        $tree = $event->tree;
        $handle = $tree->handle();

        if (! Sidecar::manages($handle)) {
            return;
        }

        $driver = Sidecar::driver($handle);

        if (! $driver->usesNestedFolders()) {
            return;
        }

        $touchedDirectories = [];
        $dirty = false;

        $this->syncBranches(
            $tree->tree(),
            $driver,
            $touchedDirectories,
            $dirty
        );

        $this->deleteEmptyDirectories(
            $touchedDirectories,
            Path::tidy($tree->collection()->resolvedDirectory())
        );

        if ($dirty) {
            $driver->afterTreeSynced($tree);
        }
    }

    protected function syncBranches(array $branches, Driver $driver, array &$touchedDirectories, bool &$dirty): void
    {
        foreach (array_values($branches) as $index => $branch) {
            $id = $branch['entry'] ?? null;

            if (! $id || ! $entry = EntryFacade::find($id)) {
                continue;
            }

            if ($this->syncEntry($entry, $driver, $index + 1, $touchedDirectories)) {
                $dirty = true;
            }

            if (! empty($branch['children'])) {
                $this->syncBranches($branch['children'], $driver, $touchedDirectories, $dirty);
            }
        }
    }

    protected function syncEntry(Entry $entry, Driver $driver, int $position, array &$touchedDirectories): bool
    {
        $dirty = false;
        $originalPath = $entry->path();

        $driver->syncOrder($entry, $position);

        $expectedPath = Path::tidy($entry->buildPath());

        if ($originalPath && Path::tidy($originalPath) !== $expectedPath) {
            $touchedDirectories[] = Path::tidy(dirname($originalPath));
            $touchedDirectories[] = Path::tidy(dirname($expectedPath));
            $dirty = true;
        } elseif ($entry->isDirty()) {
            $dirty = true;
        }

        if (! $dirty) {
            return false;
        }

        $entry->saveQuietly();

        return true;
    }

    protected function deleteEmptyDirectories(array $directories, string $collectionDirectory): void
    {
        $collectionDirectory = Path::tidy(rtrim($collectionDirectory, '/'));

        collect($directories)
            ->filter()
            ->flatMap(function ($directory) use ($collectionDirectory) {
                $directory = Path::tidy(rtrim($directory, '/'));
                $dirs = [];

                while (
                    $directory
                    && $directory !== $collectionDirectory
                    && Str::startsWith($directory, $collectionDirectory.'/')
                ) {
                    $dirs[] = $directory;
                    $directory = Path::tidy(dirname($directory));
                }

                return $dirs;
            })
            ->unique()
            ->sortByDesc(fn ($dir) => substr_count($dir, '/'))
            ->each(function ($directory) {
                if (! File::exists($directory) || ! File::isDirectory($directory)) {
                    return;
                }

                if (! File::isEmpty($directory)) {
                    return;
                }

                File::delete($directory);
            });
    }
}
