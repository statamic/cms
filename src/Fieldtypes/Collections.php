<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\CollectionType;

class Collections extends Relationship
{
    protected $categories = ['relationship'];
    protected $canEdit = false;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $statusIcons = false;

    protected function toItemArray($id, $site = null)
    {
        if ($collection = Collection::findByHandle($id)) {
            return [
                'title' => $collection->title(),
                'id' => $collection->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Collection::all()->sortBy('title')->map(function ($collection) {
            return [
                'id' => $collection->handle(),
                'title' => $collection->title(),
                'entries' => $collection->queryEntries()->count(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('entries'),
        ];
    }

    protected function augmentValue($value)
    {
        return Collection::findByHandle($value);
    }

    public function toGqlType()
    {
        $type = GraphQL::type(CollectionType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
