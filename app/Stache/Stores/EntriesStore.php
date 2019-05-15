<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Arr;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Statamic\Stache\Exceptions\StoreExpiredException;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class EntriesStore extends AggregateStore
{
    protected $localizationQueue = [];

    public function key()
    {
        return 'entries';
    }

    public function getItemsFromCache($cache)
    {
        $entries = collect();

        if ($cache->isEmpty()) {
            return $entries;
        }

        $collection = Collection::whereHandle(Arr::first($cache)['collection']);

        // The collection has been deleted.
        throw_unless($collection, new StoreExpiredException);

        return $cache->map(function ($item, $id) use ($collection) {
            $entry = Entry::make()
                ->id($id)
                ->collection($collection)
                ->locale($item['locale'])
                ->slug($item['slug'])
                ->date($item['date'])
                ->data($item['data'])
                ->published($item['published'])
                ->initialPath($item['path']);

            if ($item['origin']) {
                $this->localizationQueue[] = [
                    'origin' => $item['origin'],
                    'localization' => $entry,
                ];
            }

            return $entry;
        });
    }

    public function getCacheableMeta()
    {

    }

    public function getCacheableItems()
    {

    }


    public function createItemFromFile($path, $contents)
    {
        $site = Site::default()->handle();
        $collection = pathinfo($path, PATHINFO_DIRNAME);
        $collection = str_after($collection, $this->directory);

        if (Site::hasMultiple()) {
            list($collection, $site) = explode('/', $collection);
        }

        // Support entries within subdirectories at any level.
        if (str_contains($collection, '/')) {
            $collection = str_before($collection, '/');
        }

        $data = YAML::parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = $this->stache->generateId();
        }

        $collectionHandle = $collection;
        $collection = Collection::whereHandle($collection);

        if (! $entry = $this->store($collectionHandle)->getItem($id)) {
            $entry = Entry::make()
                ->id($id)
                ->collection($collection);
        }

        $slug = pathinfo(Path::clean($path), PATHINFO_FILENAME);

        if ($origin = Arr::pull($data, 'origin')) {
            $this->localizationQueue[] = [
                'origin' => $origin,
                'localization' => $entry,
            ];
        }

        $entry
            ->locale($site)
            ->slug($slug)
            ->initialPath($path)
            ->published(array_pull($data, 'published', true))
            ->data($data);

        if ($collection->dated()) {
            $entry->date(app('Statamic\Contracts\Data\Content\OrderParser')->getEntryOrder($path));
        }

        if (isset($idGenerated)) {
            $entry->save();
        }

        return $entry;
    }

    public function getItemKey($item, $path)
    {
        return $item->collectionHandle() . '::' . $item->id();
    }

    public function filter($file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        if (! Collection::whereHandle(explode('/', $relative)[0])) {
            return false;
        }

        return $file->getExtension() !== 'yaml' && substr_count($relative, '/') > 0;
    }

    public function save($entry)
    {
        File::put($path = $entry->path(), $entry->fileContents());

        if (($initial = $entry->initialPath()) && $path !== $initial) {
            File::delete($entry->initialPath()); // TODO: Test
        }
    }

    public function delete($entry)
    {
        File::delete($entry->path());
    }

    public function loadingComplete()
    {
        foreach ($this->localizationQueue as $item) {
            $origin = Entry::find($item['origin'])->addLocalization($item['localization']);
            $this->setItem($this->getItemKey($origin, ''), $origin);
        }
    }
}
