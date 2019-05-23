<?php

namespace Statamic\Stache\Stores;

use Statamic\API;
use Statamic\API\File;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Statamic\Contracts\Data\Structures\Structure;

class StructuresStore extends BasicStore
{
    protected $entryUris;
    protected $treeQueue = [];

    public function __construct(Stache $stache, Filesystem $files)
    {
        parent::__construct($stache, $files);

        $this->entryUris = collect();
        $this->forEachSite(function ($site) {
            $this->entryUris->put($site, collect());
        });
    }

    public function key()
    {
        return 'structures';
    }

    public function getItemsFromCache($cache)
    {
        return $cache->map(function ($item, $handle) {
            $structure = API\Structure::make()
                ->title($item['title'])
                ->handle($item['handle'])
                ->sites($item['sites'])
                ->initialPath($item['path']);

            foreach ($item['trees'] as $site => $tree) {
                $structure->addTree(
                    $structure
                        ->makeTree($site)
                        ->route($tree['route'])
                        ->root($tree['root'])
                        ->tree($tree['tree'] ?? [])
                        ->initialPath($tree['path'])
                );
            }

            return $structure;
        });
    }

    public function createItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);
        $relative = str_after($path, $this->directory);
        $handle = str_before($relative, '.yaml');

        return Site::hasMultiple()
            ? $this->createMultiSiteStructureFromFile($handle, $path, $data)
            : $this->createSingleSiteStructureFromFile($handle, $path, $data);
    }

    protected function createSingleSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this
            ->createBaseStructureFromFile($handle, $path, $data)
            ->sites([$site = Site::default()->handle()])
            ->collections($data['collections'] ?? null);

        return $structure->addTree(
            $structure
                ->makeTree($site)
                ->route($data['route'] ?? null)
                ->root($data['root'] ?? null)
                ->tree($data['tree'] ?? [])
        );
    }

    protected function createMultiSiteStructureFromFile($handle, $path, $data)
    {
        return substr_count($handle, '/') === 0
            ? $this->createBaseStructureFromFile($handle, $path, $data)
            : $this->createStructureTreeFromFile($handle, $path, $data);
    }

    protected function createBaseStructureFromFile($handle, $path, $data)
    {
        $structure = API\Structure::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->sites($data['sites'] ?? null)
            ->collections($data['collections'] ?? null)
            ->initialPath($path);

        // // If the base set file was modified, its localizations will already exist in the Stache.
        // // We should get those existing localizations and add it to this newly created set.
        // // Otherwise, the localizations would just disappear since they'd no longer be linked.
        // $existing = $this->items->first(function ($global) use ($handle) {
        //     return $global->handle() === $handle;
        // });

        // if ($existing) {
        //     $existing->localizations()->each(function ($localization) use ($structure) {
        //         $structure->addLocalization($localization);
        //     });
        // }

        return $structure;
    }

    protected function createStructureTreeFromFile($handle, $path, $data)
    {
        list($site, $handle) = explode('/', $handle);

        $structure = $this->items->first(function ($structure) use ($handle) {
            return $structure->handle() === $handle;
        });

        if (!$structure->sites()->contains($site)) {
            // If this file is for a site that the structure
            // isn't configure to use, just ignore it.
            return $structure;
        }

        $tree = $structure
            ->makeTree($site)
            ->route($data['route'] ?? null)
            ->root($data['root'] ?? null)
            ->tree($data['tree'] ?? []);

        return $structure->addTree($tree);
    }

    public function getItemKey($item, $path)
    {
        return pathinfo($path)['filename'];
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function save(Structure $structure)
    {
        File::put($structure->path(), $structure->fileContents());

        if (! Site::hasMultiple()) {
            return;
        }

        foreach ($structure->trees() as $tree) {
            File::put($tree->path(), $tree->fileContents());
        }

        // TODO: Any localizations that exist on disk but don't
        // exist in the structure should be deleted.
    }

    protected function toSaveableArray($structure)
    {
        $data = $structure->data();

        $data['tree'] = $this->removeEmptyChildren($data['tree']);

        return $data;
    }

    protected function removeEmptyChildren($array)
    {
        return collect($array)->map(function ($item) {
            $item['children'] = $this->removeEmptyChildren(array_get($item, 'children', []));

            if (empty($item['children'])) {
                unset($item['children']);
            }

            return $item;
        })->all();
    }

    public function getKeyFromUri(string $uri, string $site): ?string
    {
        if ($key = $this->getEntryUris($site)->flip()->get($uri)) {
            return $key;
        }

        return null;
    }

    public function getCacheableMeta()
    {
        return array_merge(parent::getCacheableMeta(), [
            'entryUris' => $this->entryUris->toArray()
        ]);
    }

    public function loadMeta($data)
    {
        parent::loadMeta($data);

        $this->withoutMarkingAsUpdated(function () use ($data) {
            $this->setEntryUris($data['entryUris']);
        });
    }

    public function setEntryUris($uris)
    {
        $this->entryUris = collect($uris);
    }

    public function getEntryUris($site = null)
    {
        $site = $site ?? $this->stache->sites()->first();

        return collect($this->entryUris->get($site));
    }

    public function setItem($key, $item)
    {
        parent::setItem($key, $item);

        $this->treeQueue[] = $item;

        return $this;
    }

    public function removeItem($key)
    {
        parent::removeItem($key);

        $this->flushStructureEntryUris($key);

        return $this;
    }

    protected function flushStructureEntryUris($handle)
    {
        foreach ($this->stache->sites() as $site) {
            $this->entryUris->put($site, collect($this->entryUris->get($site))->reject(function ($uri, $key) use ($handle) {
                return str_before($key, '::') === $handle;
            }));
        }
    }

    public function loadingComplete()
    {
        collect($this->treeQueue)->unique()->each(function ($structure) {
            $this->flushStructureEntryUris($structure->handle());

            foreach ($structure->trees() as $tree) {
                foreach ($tree->uris() as $key => $uri) {
                    $this->entryUris
                        ->get($tree->locale())
                        ->put($tree->handle() . '::' . $key, $uri);
                }
            }
        });
    }
}
