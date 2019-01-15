<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Statamic\Data\Entries\LocalizedEntry;
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

        foreach ($cache as $id => $item) {
            $entry = $entries->get($id) ?? Entry::make()
                ->id($id)
                ->collection(Collection::whereHandle($item['collection']));

            foreach ($item['localizations'] as $site => $attributes) {
                $localized = (new LocalizedEntry)
                    ->id($id)
                    ->locale($site)
                    ->slug($attributes['slug'])
                    ->initialPath($attributes['path'])
                    ->published($attributes['published'])
                    ->order($attributes['order'])
                    ->data($attributes['data']);

                $entry->addLocalization($localized);
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
        $slug = pathinfo(Path::clean($path), PATHINFO_FILENAME);

        $localized = (new LocalizedEntry)
            ->id($id = array_pull($data, 'id'))
            ->locale($site)
            ->slug($slug)
            ->initialPath($path)
            ->published(array_pull($data, 'published', true))
            ->order(app('Statamic\Contracts\Data\Content\OrderParser')->getEntryOrder($path))
            ->data($data);

        if (! $entry = $this->store($collection)->getItem($id)) {
            $entry = Entry::make()
                ->id($id)
                ->collection(Collection::whereHandle($collection));
        }

        $entry->addLocalization($localized);

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

        return $file->getExtension() !== 'yaml' && substr_count($relative, '/') > 0;
    }

    public function save($entry)
    {
        File::put($path = $entry->path(), $entry->fileContents());

        if (($initial = $entry->initialPath()) && $path !== $initial) {
            File::delete($entry->initialPath()); // TODO: Test
        }
    }
}
