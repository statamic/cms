<?php

namespace Statamic\Widgets;

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Column;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use function Statamic\trans as __;

class Collection extends Widget
{
    public function component()
    {
        $collection = $this->config('collection');

        if (! CollectionAPI::handleExists($collection)) {
            return VueComponent::render('dynamic-html-renderer', [
                'html' => "Error: Collection [$collection] doesn't exist.",
            ]);
        }

        $collection = CollectionAPI::findByHandle($collection);

        if (! User::current()->can('view', $collection)) {
            return;
        }

        [$sortColumn, $sortDirection] = $this->parseSort($collection);

        $blueprints = $collection
            ->entryBlueprints()
            ->reject->hidden()
            ->map(function ($blueprint) {
                return [
                    'handle' => $blueprint->handle(),
                    'title' => __($blueprint->title()),
                ];
            })->values();

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

        return VueComponent::render('collection-widget', [
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
            'blueprints' => $blueprints->map(fn ($blueprint) => [
                ...$blueprint,
                'createEntryUrl' => cp_route('collections.entries.create', [$collection->handle(), Site::selected(), 'blueprint' => $blueprint['handle']]),
            ])->all(),
            'listingUrl' => cp_route('collections.show', $collection),
        ]);
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
