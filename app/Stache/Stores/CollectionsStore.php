<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class CollectionsStore extends BasicStore
{
    public function key()
    {
        return 'collections';
    }

    public function createItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);

        $sites = array_get($data, 'sites', Site::hasMultiple() ? [] : [Site::default()->handle()]);

        $collection = Collection::create($handle)
            ->title(array_get($data, 'title'))
            ->route(array_get($data, 'route'))
            ->dated(array_get($data, 'date', false))
            ->ampable(array_get($data, 'amp', false))
            ->sites($sites)
            ->template(array_get($data, 'template'))
            ->layout(array_get($data, 'layout'))
            ->data(array_get($data, 'data'))
            ->entryBlueprints(array_get($data, 'blueprints'))
            ->searchIndex(array_get($data, 'search_index'))
            ->revisionsEnabled(array_get($data, 'revisions'))
            ->structure(array_get($data, 'structure'));

        if (array_get($data, 'orderable', false)) {
            $positions = array_get($data, 'entry_order', []);
            array_unshift($positions, null);
            unset($positions[0]);
            $collection
                ->orderable(true)
                ->setEntryPositions($positions);
        }

        if ($dateBehavior = array_get($data, 'date_behavior')) {
            $collection
                ->futureDateBehavior($dateBehavior['future'] ?? null)
                ->pastDateBehavior($dateBehavior['past'] ?? null);
        }

        return $collection;
    }

    public function getItemKey($item, $path)
    {
        return pathinfo($path)['filename'];
    }

    public function filter($file)
    {
        $relative = $file->getPathname();

        $dir = str_finish($this->directory, '/');

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function save(CollectionContract $collection)
    {
        $this->files->put($collection->path(), $collection->fileContents());
    }

    public function delete(CollectionContract $collection)
    {
        $this->files->delete($collection->path());
    }

    public function removeByPath($path)
    {
        parent::removeByPath($path);

        $collection = $this->getItemKey(null, $path);

        $this->stache->store('entries::'.$collection)->markAsExpired();

        return $this;
    }

    public function setItem($key, $item)
    {
        if ($this->markUpdates) {
            optional($item->structure())->updateEntryUris();
        }

        return parent::setItem($key, $item);
    }
}
