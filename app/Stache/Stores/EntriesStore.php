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

        foreach ($cache as $id => $item) {
            $entry = $entries->get($id) ?? Entry::make()
                ->id($id)
                ->collection($collection);

            foreach ($item['localizations'] as $site => $attributes) {
                $entry->in($site, function ($localized) use ($attributes, $collection) {
                    $localized
                        ->slug($attributes['slug'])
                        ->initialPath($attributes['path'])
                        ->published($attributes['published'])
                        ->data($attributes['data']);

                    if ($collection->dated()) {
                        $localized->date($attributes['date']);
                    }
                });
            }

            $entries[$id] = $entry;
        }

        return $entries;
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

        $localized = $entry->in($site, function ($localized) use ($data, $path, $collection) {
            $slug = pathinfo(Path::clean($path), PATHINFO_FILENAME);

            $localized
                ->slug($slug)
                ->initialPath($path)
                ->published(array_pull($data, 'published', true))
                ->data($data);

            if ($collection->dated()) {
                $localized->date(app('Statamic\Contracts\Data\Content\OrderParser')->getEntryOrder($path));
            }
        });

        if (isset($idGenerated)) {
            $localized->save();
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
}
