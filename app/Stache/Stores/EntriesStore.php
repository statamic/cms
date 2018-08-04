<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Entry;

class EntriesStore extends AggregateStore
{
    public function key()
    {
        return 'entries';
    }

    public function getItemsFromCache($cache)
    {
        dd('todo CollectionsStore@getItemsFromCache', $cache);
    }

    public function getCacheableMeta()
    {

    }

    public function getCacheableItems()
    {

    }


    public function createItemFromFile($path, $contents)
    {
        $collection = pathinfo($path, PATHINFO_DIRNAME);
        $collection = str_after($collection, $this->directory);
        // Support entries within subdirectories at any level.
        if (str_contains($collection, '/')) {
            $collection = str_before($collection, '/');
        }

        $data = YAML::parse($contents);
        $slug = pathinfo(Path::clean($path), PATHINFO_FILENAME);

        return Entry::create($slug)
            ->collection($collection)
            ->with($data)
            ->published(true) // @todo
            ->order(null) // @todo
            ->get();
    }

    public function getItemKey($item, $path)
    {
        return $item->collectionName() . '::' . $item->id();
    }

    public function filter($file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        return $file->getExtension() !== 'yaml' && substr_count($relative, '/') > 0;
    }
}
