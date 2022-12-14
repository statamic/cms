<?php

namespace Statamic\Widgets;

use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Scope;
use Statamic\Facades\User;

class Collection extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $collection = $this->config('collection');

        if (! CollectionAPI::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        $collection = CollectionAPI::findByHandle($collection);

        if (! User::current()->can('view', $collection)) {
            return;
        }

        [$sortColumn, $sortDirection] = $this->parseSort($collection);

        return view('statamic::widgets.collection', [
            'collection' => $collection,
            'filters' => Scope::filters('entries', [
                'collection' => $collection->handle(),
            ]),
            'title' => $this->config('title', $collection->title()),
            'button' => $collection->createLabel(),
            'blueprints' => $collection->entryBlueprints()->reject->hidden()->values(),
            'limit' => $this->config('limit', 5),
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
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
