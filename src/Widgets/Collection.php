<?php

namespace Statamic\Widgets;

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Column;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Scope;
use Statamic\Facades\User;

class Collection extends Widget
{
    public function component()
    {
        $collection = $this->getCollection();

        if (! User::current()->can('view', $collection)) {
            return null;
        }

        return 'collection-widget';
    }

    public function with()
    {
        $collection = $this->getCollection();

        [$sortColumn, $sortDirection] = $this->parseSort($collection);

        $blueprint = $collection->entryBlueprint();
        $columns = $blueprint
            ->columns()
            ->put('status', Column::make('status')
                ->listable(true)
                ->visible(true)
                ->defaultVisibility(true)
                ->sortable(false))
            ->only($this->config('fields', []))
            ->map(fn ($column) => $column->sortable(false)->visible(true))
            ->values();

        return [
            'collection' => $collection->handle(),
            'title' => $this->config('title', $collection->title()),
            'additionalColumns' => $columns,
            'filters' => Scope::filters('entries', [
                'collection' => $collection->handle(),
            ]),
            'initialSortColumn' => $sortColumn,
            'initialSortDirection' => $sortDirection,
            'initialPerPage' => $this->config('limit', 5),
            'canCreate' => User::current()->can('create', [EntryContract::class, $collection]) && $collection->hasVisibleEntryBlueprint(),
            'createLabel' => $collection->createLabel(),
            'blueprints' => $collection->entryBlueprints()->reject->hidden()->values(),
            'listingUrl' => cp_route('collections.show', $collection),
        ];
    }

    protected function getCollection()
    {
        $collection = $this->config('collection');

        if (! CollectionAPI::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        return CollectionAPI::findByHandle($collection);
    }

    /**
     * Parse sort column and direction, similar to how sorting works on collection tag.
     *
     * @param  \Statamic\Entries\Collection  $collection
     * @return array
     */
    protected function parseSort($collection)
    {
        $default = $collection->dated() ? 'date:desc' : 'title:asc';
        $sort = $this->config('order_by') ?? $this->config('sort') ?? $default;
        $exploded = explode(':', $sort);
        $column = $exploded[0];
        $direction = $exploded[1] ?? 'asc';

        return [$column, $direction];
    }
}
