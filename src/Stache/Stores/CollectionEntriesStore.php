<?php

namespace Statamic\Stache\Stores;

use Statamic\Entries\GetDateFromPath;
use Statamic\Entries\GetSlugFromPath;
use Statamic\Entries\GetSuffixFromPath;
use Statamic\Entries\RemoveSuffixFromPath;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class CollectionEntriesStore extends ChildStore
{
    protected $collection;
    private bool $shouldBlinkEntryUris = true;

    protected function collection()
    {
        return $this->collection ?? Collection::findByHandle($this->childKey);
    }

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = Str::finish($this->directory(), '/');
        $relative = Path::tidy($file->getPathname());

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        if (Site::multiEnabled()) {
            [$site, $relative] = explode('/', $relative, 2);
            if (! $this->collection()->sites()->contains($site)) {
                return false;
            }
        }

        // if (! Collection::findByHandle(explode('/', $relative)[0])) {
        //     return false;
        // }

        return $file->getExtension() !== 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        [$collection, $site] = $this->extractAttributesFromPath($path);

        $data = YAML::file($path)->parse($contents);

        if (! $id = Arr::pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $collectionHandle = $collection;
        $collection = Collection::findByHandle($collectionHandle);

        $entry = Entry::make()
            ->id($id)
            ->collection($collection);

        if ($origin = Arr::pull($data, 'origin')) {
            $entry->origin($origin);
        }

        $entry
            ->blueprint($data['blueprint'] ?? null)
            ->locale($site)
            ->initialPath($path)
            ->published(Arr::pull($data, 'published', true))
            ->data($data);

        $slug = (new GetSlugFromPath)($path);

        if (! $collection->requiresSlugs() && $slug == $id) {
            $entry->slug(null);
        } else {
            $entry->slug($slug);
        }

        // if ($collection->orderable() && ! $collection->getEntryPosition($id)) {
        //     $positionGenerated = true;
        //     $collection->appendEntryPosition($id)->save();
        // }

        if ($collection->dated()) {
            $entry->date((new GetDateFromPath)($path));
        }

        // Blink the entry so that it can be used when building the URI. If it's not
        // in there, it would try to retrieve the entry, which doesn't exist yet.
        Blink::store('structure-entries')->put($id, $entry);

        if (isset($idGenerated) || isset($positionGenerated)) {
            $this->writeItemToDiskWithoutIncrementing($entry);
        }

        return $entry;
    }

    protected function extractAttributesFromPath($path)
    {
        $site = Site::default()->handle();
        $collection = pathinfo($path, PATHINFO_DIRNAME);
        $collection = Str::after($collection, $this->parent->directory());

        if (Site::multiEnabled()) {
            [$collection, $site] = explode('/', $collection);
        }

        // Support entries within subdirectories at any level.
        if (Str::contains($collection, '/')) {
            $collection = Str::before($collection, '/');
        }

        return [$collection, $site];
    }

    protected function handleModifiedItem($item)
    {
        $item->taxonomize();
    }

    protected function handleDeletedItem($path, $id)
    {
        [$collection, $site] = $this->extractAttributesFromPath($path);

        if ($collection = Collection::findByHandle($collection)) {
            $this->removeEntryFromStructure($collection, $id);
        }
    }

    protected function removeEntryFromStructure($collection, $id)
    {
        if (! $collection->hasStructure()) {
            return;
        }

        $collection->structure()->trees()->each(function ($tree) use ($id) {
            $tree
                ->remove($id)
                ->save();
        });
    }

    protected function storeIndexes()
    {
        $indexes = collect([
            'slug',
            'uri',
            'collectionHandle',
            'published',
            'title',
            'site' => Indexes\Site::class,
            'origin' => Indexes\Origin::class,
            'parent' => Indexes\Parents::class,
        ]);

        if (! $collection = Collection::findByHandle($this->childKey())) {
            return $indexes->all();
        }

        if ($collection->orderable()) {
            $indexes[] = 'order';
        }

        if ($collection->dated()) {
            $indexes[] = 'date';
        }

        return $indexes->merge(
            $collection->taxonomies()->map->handle()
        )->all();
    }

    protected function writeItemToDiskWithoutIncrementing($item)
    {
        $item->writeFile($item->path());
    }

    protected function writeItemToDisk($item)
    {
        $basePath = $item->buildPath();
        $suffixlessPath = (new RemoveSuffixFromPath)($item->path());

        if ($basePath !== $suffixlessPath) {
            // If the path should change (e.g. a new slug or date) then
            // reset the counter to 1 so the suffix doesn't get maintained.
            $num = 0;
        } else {
            // Otherwise, start from whatever the suffix was.
            $num = (new GetSuffixFromPath)($item->path()) ?? 0;
        }

        while (true) {
            $ext = '.'.$item->fileExtension();
            $filename = Str::beforeLast($basePath, $ext);
            $suffix = $num ? ".$num" : '';
            $path = "{$filename}{$suffix}{$ext}";

            if (! $contents = File::get($path)) {
                break;
            }

            $itemFromDisk = $this->makeItemFromFile($path, $contents);

            if ($item->id() == $itemFromDisk->id()) {
                break;
            }

            $num++;
        }

        $item->writeFile($path);
    }

    protected function getCachedItem($key)
    {
        $cacheKey = $this->getItemCacheKey($key);

        if (! $entry = Stache::cacheStore()->get($cacheKey)) {
            return null;
        }

        $isLoadingIds = Index::currentlyLoading() === $this->key().'/id';

        if (! $isLoadingIds && $this->shouldBlinkEntryUris && ($uri = $this->resolveIndex('uri')->load()->get($entry->id()))) {
            Blink::store('entry-uris')->put($entry->id(), $uri);
        }

        return $entry;
    }

    public function withoutBlinkingEntryUris($callback)
    {
        $this->shouldBlinkEntryUris = false;
        $return = $callback();
        $this->shouldBlinkEntryUris = true;

        return $return;
    }

    public function updateUris($ids = null)
    {
        $this->updateEntriesWithinIndex($this->index('uri'), $ids);
        $this->updateEntriesWithinStore($ids);
    }

    public function updateOrders($ids = null)
    {
        $this->updateEntriesWithinIndex($this->index('order'), $ids);
    }

    public function updateParents($ids = null)
    {
        $this->updateEntriesWithinIndex($this->index('parent'), $ids);
    }

    private function updateEntriesWithinIndex($index, $ids)
    {
        if (empty($ids)) {
            return $index->update();
        }

        collect($ids)
            ->map(fn ($id) => Entry::find($id))
            ->filter()
            ->each(fn ($entry) => $index->updateItem($entry));
    }

    private function updateEntriesWithinStore($ids)
    {
        if (empty($ids)) {
            $ids = $this->paths()->keys();
        }

        $entries = $this->withoutBlinkingEntryUris(fn () => collect($ids)->map(fn ($id) => Entry::find($id))->filter());

        $entries->each(fn ($entry) => $this->cacheItem($entry));
    }
}
