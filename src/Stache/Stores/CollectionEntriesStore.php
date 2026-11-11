<?php

namespace Statamic\Stache\Stores;

use Closure;
use Illuminate\Support\Carbon;
use Statamic\Entries\Entry as EntryInstance;
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
use WeakReference;

class CollectionEntriesStore extends ChildStore
{
    protected $collection;
    private bool $shouldBlinkEntryUris = true;
    private int $makingItemsInBulk = 0;
    private array $collectionsForBulk = [];

    public function handleFileChanges()
    {
        // Entries get their uris, parents and order from the collection trees, so a tree file that
        // changed on disk has to be picked up before this store's indexes are used. (Each store
        // only checks once per request, so this can't loop.)
        if (Stache::isWatcherEnabled() && ! $this->fileChangesHandled && $this->collection()?->hasStructure()) {
            Stache::store('collection-trees')?->handleFileChanges();
        }

        $this->makeItemsInBulk(fn () => parent::handleFileChanges());
    }

    public function paths()
    {
        return $this->makeItemsInBulk(fn () => parent::paths());
    }

    /**
     * While many files are being turned into entries at once, look up their
     * collection once rather than once per file. They all share the same one.
     */
    private function makeItemsInBulk(callable $callback)
    {
        $this->makingItemsInBulk++;

        try {
            return $callback();
        } finally {
            if (--$this->makingItemsInBulk === 0) {
                $this->collectionsForBulk = [];
            }
        }
    }

    private function findCollection($handle)
    {
        if (! $this->makingItemsInBulk) {
            return Collection::findByHandle($handle);
        }

        return $this->collectionsForBulk[$handle] ??= Collection::findByHandle($handle);
    }

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
        $collection = $this->findCollection($collectionHandle);

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
            $entry->date($this->getDateFromPath($path));
        }

        // Blink the entry so that it can be used when building the URI. If it's not
        // in there, it would try to retrieve the entry, which doesn't exist yet.
        Blink::store('structure-entries')->put($id, $entry);

        if (isset($idGenerated) || isset($positionGenerated)) {
            $this->writeItemToDiskWithoutIncrementing($entry);
        }

        $this->takeSnapshot($entry);

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
            'collectionHandle',
            'published',
            'title',
            'site',
            'origin' => Indexes\Origin::class,
            'parent' => Indexes\Parents::class,
            'uri',
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

            // Only parse the ID from the file rather than using makeItemFromFile,
            // which would put a stale entry into the structure-entries blink store.
            $id = Arr::get(YAML::file($path)->parse($contents), 'id');

            if ($item->id() == $id) {
                break;
            }

            $num++;
        }

        $item->writeFile($path);
    }

    protected function getCachedItem($key)
    {
        if (! $entry = $this->getSnapshotCopy($key) ?? Stache::cacheStore()->get($this->getItemCacheKey($key))) {
            return null;
        }

        if (! Index::isLoading() && $this->shouldBlinkEntryUris && ($uri = $this->resolveIndex('uri')->load()->get($entry->id()))) {
            Blink::store('entry-uris')->put($entry->id(), $uri);
        }

        return $entry;
    }

    protected function cacheItem($item)
    {
        parent::cacheItem($item);

        $key = $this->getItemKey($item);
        $snapshot = $this->snapshots()->get($key);

        // The first time the entry built from the file is cached, the cache holds exactly
        // what the snapshot holds. Anything cached after that may differ from it.
        if ($snapshot && ! $snapshot['cached'] && $snapshot['source']->get() === $item) {
            $snapshot['cached'] = true;
            $this->snapshots()->put($key, $snapshot);
        } else {
            $this->snapshots()->forget($key);
        }
    }

    public function forgetItem($key)
    {
        $this->snapshots()->forget($key);

        parent::forgetItem($key);
    }

    private function takeSnapshot(EntryInstance $entry): void
    {
        $this->snapshots()->put($entry->id(), [
            'source' => WeakReference::create($entry),
            'snapshot' => $this->copy($entry),
            'cached' => false,
        ]);
    }

    /**
     * Snapshots are only good for the request they were taken in, since another process can
     * cache a different version of an entry at any time. This store can outlive a request
     * (it does under Octane), but Blink is new for each one, so they are kept there.
     */
    private function snapshots()
    {
        return Blink::store('entry-snapshots::'.$this->key());
    }

    public function resetMemoizedState()
    {
        parent::resetMemoizedState();

        // Blink isn't cleared between a worker's jobs, so the snapshots
        // would otherwise last for as long as the worker does.
        $this->snapshots()->flush();
    }

    /**
     * While indexes are being built, hand out a copy of the snapshot taken when the entry
     * was made from its file, rather than unserialising a second full copy from the cache.
     * Its data arrays are shared with the snapshot until the caller changes them.
     */
    private function getSnapshotCopy($key): ?EntryInstance
    {
        if (! Index::isLoading()) {
            return null;
        }

        $snapshot = $this->snapshots()->get($key);

        if (! $snapshot || ! $snapshot['cached']) {
            return null;
        }

        return $this->copy($snapshot['snapshot']);
    }

    private function copy(EntryInstance $entry): EntryInstance
    {
        $copy = clone $entry;

        Closure::bind(function () {
            // A copy read from the cache doesn't carry these (see Entry::__sleep).
            $this->cachedKeys = $this->computedCallbackCache = $this->siteCache = $this->augmentationReferenceKey = null;

            // Carbon is mutable, so the copy needs its own date.
            if (is_object($this->date)) {
                $this->date = clone $this->date;
            }
        }, $copy, EntryInstance::class)();

        return $copy;
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
        if (collect($ids)->isEmpty()) {
            return $index->update();
        }

        collect($ids)
            ->map(fn ($id) => Entry::find($id))
            ->filter()
            ->each(fn ($entry) => $index->updateItem($entry));
    }

    private function updateEntriesWithinStore($ids)
    {
        if (collect($ids)->isEmpty()) {
            $ids = $this->paths()->keys();
        }

        $entries = $this->withoutBlinkingEntryUris(fn () => collect($ids)->map(fn ($id) => Entry::find($id))->filter());

        $entries->each(fn ($entry) => $this->cacheItem($entry));
    }

    private function getDateFromPath($path)
    {
        if (! $date = (new GetDateFromPath)($path)) {
            return null;
        }

        $format = match (strlen($date)) {
            10 => 'Y-m-d',
            15 => 'Y-m-d-Hi',
            17 => 'Y-m-d-His',
        };

        $carbon = Carbon::createFromFormat($format, $date, config('app.timezone'));

        if (strlen($date) === 10) {
            $carbon->startOfDay();
        }

        return $carbon->utc();
    }
}
