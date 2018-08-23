<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

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

    public function save(EntryContract $entry)
    {
        // TODO: The logic for building a path is probably better somewhere else.
        $prefix = '';
        if ($order = $entry->order()) {
            $prefix = $order . '.';
        }
        $path = vsprintf('%s/%s/%s%s.md', [
            $this->directory,
            $entry->collectionName(),
            $prefix,
            $entry->slug()
        ]);

        // TODO: The logic for building the markdown file contents is better elsewhere
        $data = $entry->data();
        $content = array_pull($data, 'content');
        $contents = YAML::dump($data, $content);

        $this->files->put($path, $contents);
    }
}
