<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Facades\Stache;
use Statamic\Facades\Structure;
use Statamic\Facades\Collection;
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
        $relative = str_after($file->getPathname(), $dir);
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
            ->data(array_get($data, 'data'))
            ->entryBlueprints(array_get($data, 'blueprints'))
            ->searchIndex(array_get($data, 'search_index'))
            ->revisionsEnabled(array_get($data, 'revisions', false))
            ->defaultStatus(array_get($data, 'default_status'))
            ->structure(array_get($data, 'structure'))
            ->orderable(array_get($data, 'orderable', false))
            ->sortField(array_get($data, 'sort_by'))
            ->sortDirection(array_get($data, 'sort_dir'))
            ->taxonomies(array_get($data, 'taxonomies'));

        if ($dateBehavior = array_get($data, 'date_behavior')) {
            $collection
                ->futureDateBehavior($dateBehavior['future'] ?? null)
                ->pastDateBehavior($dateBehavior['past'] ?? null);
        }

        // $collection
        //     ->setEntryPositions($this->getEntryPositions($data, $collection))
        //     ->save();

        return $collection;
    }

    protected function getEntryPositions($data, $collection)
    {
        if (! array_get($data, 'orderable', false)) {
            return [];
        }

        $positions = array_get($data, 'entry_order', function () use ($collection) {
            return $collection->queryEntries()->get()->map->id()->all();
        });

        return collect($positions)->mapWithKeys(function ($id, $index) {
            return [$index + 1 => $id];
        })->all();
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
    }
}
