<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Collection;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Structure;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class CollectionsStore extends BasicStore
{
    public function key()
    {
        return 'collections';
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    public function getFileFilter(SplFileInfo $file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = str_after(Path::tidy($file->getPathname()), $dir);
        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        $sites = array_get($data, 'sites', Site::hasMultiple() ? [] : [Site::default()->handle()]);

        $collection = Collection::make($handle)
            ->title(array_get($data, 'title'))
            ->route(array_get($data, 'route'))
            ->mount(array_get($data, 'mount'))
            ->dated(array_get($data, 'date', false))
            ->ampable(array_get($data, 'amp', false))
            ->sites($sites)
            ->template(array_get($data, 'template'))
            ->layout(array_get($data, 'layout'))
            ->cascade(array_get($data, 'inject', []))
            ->entryBlueprints(array_get($data, 'blueprints'))
            ->searchIndex(array_get($data, 'search_index'))
            ->revisionsEnabled(array_get($data, 'revisions', false))
            ->defaultPublishState($this->getDefaultPublishState($data))
            ->structureContents(array_get($data, 'structure'))
            ->orderable(array_get($data, 'orderable', false))
            ->sortField(array_get($data, 'sort_by'))
            ->sortDirection(array_get($data, 'sort_dir'))
            ->taxonomies(array_get($data, 'taxonomies'));

        if ($dateBehavior = array_get($data, 'date_behavior')) {
            $collection
                ->futureDateBehavior($dateBehavior['future'] ?? null)
                ->pastDateBehavior($dateBehavior['past'] ?? null);
        }

        $collection = $this->setPositions($collection, $data);

        return $collection;
    }

    protected function getDefaultPublishState($data)
    {
        $value = array_get($data, 'default_status', 'published');

        if (! in_array($value, ['published', 'draft'])) {
            throw new \Exception('Invalid collection default_status value. Must be "published" or "draft".');
        }

        return $value === 'published';
    }

    protected function setPositions($collection, $data)
    {
        if (! $collection->orderable()) {
            return $collection;
        }

        // If it's not set, it'll work out the order automatically by querying entries when it needs to.
        if (! array_has($data, 'entry_order')) {
            return $collection;
        }

        $order = array_get($data, 'entry_order');

        // The entries are in the YAML as a simple zero-indexed array. The positions on the
        // collection object expect the keys to be the actual positions (ie. starts with 1)
        $positions = collect($order)->mapWithKeys(function ($entry, $index) {
            return [$index + 1 => $entry];
        });

        return $collection->setEntryPositions($positions);
    }

    public function updateEntryUris($collection)
    {
        Stache::store('entries')
            ->store($collection->handle())
            ->index('uri')
            ->update();
    }

    public function handleFileChanges()
    {
        if ($this->fileChangesHandled || ! config('statamic.stache.watcher')) {
            return;
        }

        parent::handleFileChanges();

        // TODO: only update structures for collections that were modified.
        Structure::all()->each->updateEntryUris();

        // TODO: only update order indexes for collections that were modified.
        Collection::all()->filter->orderable()->each(function ($collection) {
            Stache::store('entries')->store($collection->handle())->index('order')->update();
        });
    }
}
