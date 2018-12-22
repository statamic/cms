<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;

class RelationshipFieldtypeController extends CpController
{
    public function index()
    {
        return Resource::collection($this->getIndexItems())->additional(['meta' => [
            'sortColumn' => $this->getSortColumn(),
            'columns' => $this->getIndexColumns(),
        ]]);
    }

    protected function getIndexItems()
    {
        return $this->getIndexQuery()
            ->orderBy($this->getSortColumn(), $this->getSortDirection())
            ->paginate();
    }

    protected function getSortColumn()
    {
        return request('sort', 'title');
    }

    protected function getSortDirection()
    {
        return request('order', 'asc');
    }

    protected function getIndexQuery()
    {
        $query = Entry::query();

        if ($collections = request('collections')) {
            $query->whereIn('collection', $collections);
        }

        return $query;
    }

    protected function getIndexColumns()
    {
        return collect(request('columns'))->map(function ($column) {
            return ['label' => $column, 'field' => $column];
        })->all();
    }

    public function data(Request $request)
    {
        $items = collect($request->selections)->map(function ($id) {
            return $this->toItemArray($id);
        });

        return Resource::collection($items);
    }

    protected function toItemArray($id)
    {
        if ($entry = Entry::find($id)) {
            return $entry->toArray();
        }

        return $this->invalidItemArray($id);
    }

    protected function invalidItemArray($id)
    {
        return [
            'id' => $id,
            'title' => $id,
            'invalid' => true
        ];
    }
}
