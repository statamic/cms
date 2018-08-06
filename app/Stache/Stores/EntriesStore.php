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
        return $cache->map(function ($item, $id) {
            $attr = $item['attributes'];
            $data = $item['data'][default_locale()];
            unset($data['id']);

            $entry = Entry::create($attr['slug'])
                ->id($id)
                ->with($data)
                ->collection($attr['collection'])
                ->order(array_get($attr, 'order'))
                ->published(array_get($attr, 'published'))
                ->get();

            if (count($item['data']) > 1) {
                foreach ($item['data'] as $locale => $data) {
                    $entry->dataForLocale($locale, $data);
                }

                $entry->syncOriginal();
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
            ->published(app('Statamic\Contracts\Data\Content\StatusParser')->entryPublished($path))
            ->order(app('Statamic\Contracts\Data\Content\OrderParser')->getEntryOrder($path))
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
