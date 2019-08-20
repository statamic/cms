<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Collection;

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

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);

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
            ->revisionsEnabled(array_get($data, 'revisions'))
            ->defaultStatus(array_get($data, 'default_status'))
            ->structure(array_get($data, 'structure'))
            ->orderable(array_get($data, 'orderable', false));
            // ->taxonomies(array_get($data, 'taxonomies'));

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
}
