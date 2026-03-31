<?php

namespace Statamic\Stache;

use Illuminate\Support\Collection;
use Statamic\Support\Str;

class GitPathMapper
{
    /**
     * Map a collection of git diff changes to stache actions.
     *
     * Each change is an array with keys:
     *   - 'status': A|M|D (added, modified, deleted)
     *   - 'path':   path relative to the git root / base_path()
     *
     * Each returned action is an array with keys:
     *   - 'type':         'update-item' | 'forget-item' | 'warm-store' | 'full-refresh'
     *   - 'storeKey':     e.g. 'entries::blog', 'collections', 'taxonomies' (null for full-refresh)
     *   - 'absolutePath': absolute filesystem path (null for full-refresh and warm-store)
     *   - 'displayPath':  relative path for display purposes
     *
     * @param  Collection<array{status: string, path: string}>  $changes
     */
    public function map(Collection $changes, string $basePath, array $storeDirectories): Collection
    {
        $basePath = rtrim($basePath, '/');

        return $changes->flatMap(function ($change) use ($basePath, $storeDirectories) {
            return $this->mapChange($change['status'], $change['path'], $basePath, $storeDirectories);
        });
    }

    private function mapChange(string $status, string $relativePath, string $basePath, array $storeDirectories): array
    {
        $absolutePath = $basePath.'/'.$relativePath;

        // --- Entries: content/collections/{collection}/...
        $entriesDir = rtrim($storeDirectories['entries'] ?? $basePath.'/content/collections', '/');
        if (Str::startsWith($absolutePath, $entriesDir.'/')) {
            $remainder = Str::after($absolutePath, $entriesDir.'/');
            $parts = explode('/', $remainder, 2);
            $collection = $parts[0];

            // e.g. content/collections/blog.yaml  → collections store (the collection config itself)
            // e.g. content/collections/blog/...   → entries::blog store
            if (isset($parts[1])) {
                return [$this->makeAction($status, 'entries::'.$collection, $absolutePath, $relativePath)];
            }
        }

        // --- Collections config: content/collections/{handle}.yaml (files in the dir root)
        $collectionsDir = rtrim($storeDirectories['collections'] ?? $basePath.'/content/collections', '/');
        if (Str::startsWith($absolutePath, $collectionsDir.'/')) {
            $remainder = Str::after($absolutePath, $collectionsDir.'/');
            // Only match files directly in the directory (not subdirectories = entry files)
            if (! Str::contains($remainder, '/') && Str::endsWith($remainder, '.yaml')) {
                return [$this->makeAction($status, 'collections', $absolutePath, $relativePath)];
            }
        }

        // --- Terms: content/taxonomies/{taxonomy}/...
        $termsDir = rtrim($storeDirectories['terms'] ?? $basePath.'/content/taxonomies', '/');
        if (Str::startsWith($absolutePath, $termsDir.'/')) {
            $remainder = Str::after($absolutePath, $termsDir.'/');
            $parts = explode('/', $remainder, 2);
            $taxonomy = $parts[0];

            if (isset($parts[1])) {
                // A term file inside a taxonomy subdirectory
                return [$this->makeAction($status, 'terms::'.$taxonomy, $absolutePath, $relativePath)];
            }
        }

        // --- Taxonomies config: content/taxonomies/{handle}.yaml (files in the dir root)
        $taxonomiesDir = rtrim($storeDirectories['taxonomies'] ?? $basePath.'/content/taxonomies', '/');
        if (Str::startsWith($absolutePath, $taxonomiesDir.'/')) {
            $remainder = Str::after($absolutePath, $taxonomiesDir.'/');
            if (! Str::contains($remainder, '/') && Str::endsWith($remainder, '.yaml')) {
                return [$this->makeAction($status, 'taxonomies', $absolutePath, $relativePath)];
            }
        }

        // --- Global variables: content/globals/{site}/{handle}.yaml (one slash in remainder)
        // --- Globals base:     content/globals/{handle}.yaml      (no slash in remainder)
        $globalsDir = rtrim($storeDirectories['globals'] ?? $basePath.'/content/globals', '/');
        if (Str::startsWith($absolutePath, $globalsDir.'/')) {
            $remainder = Str::after($absolutePath, $globalsDir.'/');
            if (Str::contains($remainder, '/')) {
                return [$this->makeAction($status, 'global-variables', $absolutePath, $relativePath)];
            } elseif (Str::endsWith($remainder, '.yaml')) {
                return [$this->makeAction($status, 'globals', $absolutePath, $relativePath)];
            }
        }

        // --- Navigation: content/navigation/{handle}.yaml
        $navigationDir = rtrim($storeDirectories['navigation'] ?? $basePath.'/content/navigation', '/');
        if (Str::startsWith($absolutePath, $navigationDir.'/')) {
            return [$this->makeAction($status, 'navigation', $absolutePath, $relativePath)];
        }

        // --- Collection trees: content/trees/collections/...
        $collectionTreesDir = rtrim($storeDirectories['collection-trees'] ?? $basePath.'/content/trees/collections', '/');
        if (Str::startsWith($absolutePath, $collectionTreesDir.'/')) {
            return [$this->makeAction($status, 'collection-trees', $absolutePath, $relativePath)];
        }

        // --- Nav trees: content/trees/navigation/...
        $navTreesDir = rtrim($storeDirectories['nav-trees'] ?? $basePath.'/content/trees/navigation', '/');
        if (Str::startsWith($absolutePath, $navTreesDir.'/')) {
            return [$this->makeAction($status, 'nav-trees', $absolutePath, $relativePath)];
        }

        // --- Asset containers: content/assets/{handle}.yaml
        $assetContainersDir = rtrim($storeDirectories['asset-containers'] ?? $basePath.'/content/assets', '/');
        if (Str::startsWith($absolutePath, $assetContainersDir.'/')) {
            return [$this->makeAction($status, 'asset-containers', $absolutePath, $relativePath)];
        }

        // --- Users: users/{email}.yaml
        $usersDir = rtrim($storeDirectories['users'] ?? $basePath.'/users', '/');
        if (Str::startsWith($absolutePath, $usersDir.'/')) {
            return [$this->makeAction($status, 'users', $absolutePath, $relativePath)];
        }

        // --- Blueprints: resources/blueprints/...
        $blueprintsDir = $basePath.'/resources/blueprints';
        if (Str::startsWith($absolutePath, $blueprintsDir.'/')) {
            return [$this->mapBlueprintChange($status, $absolutePath, $relativePath, $blueprintsDir)];
        }

        // --- Anything else is unrecognized → trigger full refresh
        return [['type' => 'full-refresh', 'storeKey' => null, 'absolutePath' => null, 'displayPath' => $relativePath]];
    }

    private function mapBlueprintChange(string $status, string $absolutePath, string $relativePath, string $blueprintsDir): array
    {
        $remainder = Str::after($absolutePath, $blueprintsDir.'/');
        $parts = explode('/', $remainder);

        // resources/blueprints/collections/{collection}/...  → warm entries::{collection} store
        if (isset($parts[0]) && $parts[0] === 'collections' && isset($parts[1])) {
            return ['type' => 'warm-store', 'storeKey' => 'entries::'.$parts[1], 'absolutePath' => null, 'displayPath' => $relativePath];
        }

        // resources/blueprints/taxonomies/{taxonomy}/...  → warm terms::{taxonomy} store
        if (isset($parts[0]) && $parts[0] === 'taxonomies' && isset($parts[1])) {
            return ['type' => 'warm-store', 'storeKey' => 'terms::'.$parts[1], 'absolutePath' => null, 'displayPath' => $relativePath];
        }

        // resources/blueprints/assets/...  → warm asset-containers store
        if (isset($parts[0]) && $parts[0] === 'assets') {
            return ['type' => 'warm-store', 'storeKey' => 'asset-containers', 'absolutePath' => null, 'displayPath' => $relativePath];
        }

        // Any other blueprint change (e.g. user.yaml, default.yaml, fieldsets) → full refresh
        return ['type' => 'full-refresh', 'storeKey' => null, 'absolutePath' => null, 'displayPath' => $relativePath];
    }

    private function makeAction(string $gitStatus, string $storeKey, string $absolutePath, string $displayPath): array
    {
        $type = $gitStatus === 'D' ? 'forget-item' : 'update-item';

        return [
            'type' => $type,
            'storeKey' => $storeKey,
            'absolutePath' => $absolutePath,
            'displayPath' => $displayPath,
        ];
    }
}
