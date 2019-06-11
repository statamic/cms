<?php

namespace Statamic\Stache\Stores;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Statamic\Contracts\Data\Structures\Structure;

class StructuresStore extends BasicStore
{
    protected $entryUris;
    protected $entryRoutes;
    protected $treeQueue = [];

    public function __construct(Stache $stache, Filesystem $files)
    {
        parent::__construct($stache, $files);

        $this->entryUris = collect();
        $this->entryRoutes = collect();
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
                ->maxDepth($item['max_depth'])
                ->collections($item['collections'])
                ->initialPath($item['path'])
                ->expectsRoot($item['expects_root']);

            foreach ($item['trees'] as $site => $tree) {
                $structure->addTree(
                    $structure
                        ->makeTree($site)
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
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null);

        return $structure->addTree(
            $structure
                ->makeTree($site)
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
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null)
            ->expectsRoot($data['expects_root'] ?? false)
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

    public function delete(Structure $structure)
    {
        File::delete($structure->path());

        foreach ($structure->trees() as $tree) {
            File::delete($tree->path());
        }
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
            'entryUris' => $this->entryUris->toArray(),
            'entryRoutes' => $this->entryRoutes->toArray(),
        ]);
    }

    public function loadMeta($data)
    {
        parent::loadMeta($data);

        $this->withoutMarkingAsUpdated(function () use ($data) {
            $this->setEntryUris($data['entryUris']);
            $this->setEntryRoutes($data['entryRoutes']);
        });
    }

    public function setEntryUris($uris)
    {
        $this->entryUris = collect($uris);
    }

    public function setEntryRoutes($routes)
    {
        $this->entryRoutes = collect($routes);
    }

    public function getEntryUris($site = null)
    {
        if ($site === null) {
            return collect($this->entryUris);
        }

        return collect($this->entryUris->get($site));
    }

    public function setItem($key, $item)
    {
        if ($this->markUpdates) {
            $this->treeQueue[] = $item;
        }

        return parent::setItem($key, $item);
    }

    public function removeItem($key)
    {
        parent::removeItem($key);

        $this->flushStructureEntryUris($key);

        return $this;
    }

    protected function flushStructureEntryUris($handle, $site = null)
    {
        $sites = $site ? [$site] : $this->stache->sites();

        foreach ($sites as $site) {
            $this->entryUris->put($site, collect($this->entryUris->get($site))->reject(function ($uri, $key) use ($handle) {
                return str_before($key, '::') === $handle;
            }));

            $this->entryRoutes->forget($handle . '::' . $site);
        }
    }

    public function loadingComplete()
    {
        collect($this->treeQueue)
            ->unique->handle()
            ->filter->collection() // Only structures linked to a collection should cause entry URIs to be updated.
            ->each->updateEntryUris();
    }

    public function updateEntryUris($structure)
    {
        foreach ($structure->trees() as $tree) {
            $locale = $tree->locale();
            $handle = $tree->handle();
            $route = $tree->route();

            if ($this->entryRoutes->get($handle . '::' . $locale) === $route) {
                // If the route hasn't changed, don't do the work again. This could happen when the
                // collection is inserted into the Stache twice. eg. Once when saving in the CP,
                // then again when the Stache notices the file changed on the next request.
                continue;
            }

            $this->flushStructureEntryUris($handle, $locale);

            $tree->flattenedPages()->filter(function ($page) {
                return $page->reference() && $page->referenceExists();
            })->each(function ($page) use ($handle, $locale) {
                $this->entryUris
                    ->get($locale)
                    ->put($handle . '::' . $page->reference(), $page->uri());
            });

            $this->entryRoutes->put($handle . '::' . $locale, $route);
        }

        $this->markAsUpdated();
    }

    public function insert($item, $key = null)
    {
        parent::insert($item, $key);

        if (Site::hasMultiple()) {
            $item->trees()->each(function ($tree) use ($key) {
                $this->setSitePath($tree->locale(), $key.'::'.$tree->locale(), $tree->path());
            });
        }

        return $this;
    }

    public function removeByPath($path)
    {
        $id = $this->getIdFromPath($path);

        if (Str::contains($id, '::')) {
            $this->removeTree($id);
        } else {
            $this->removeStructure($id);
        }
    }

    protected function removeStructure($key)
    {
        $this->removeItem($key);
        $this->flushStructureEntryUris($key);
        $this->removeSitePath($this->stache->sites()->first(), $key);
    }

    protected function removeTree($key)
    {
        [$handle, $site] = explode('::', $key);

        $this->flushStructureEntryUris($handle, $site);
        $this->removeSitePath($site, $key);

        $structure = $this->getItem($handle);
        $structure->removeTree($structure->in($site));
        $this->setItem($handle, $structure);
    }
}
