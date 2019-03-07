<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Fields\Fieldtypes\Relationship;
use Statamic\Http\Controllers\CP\CpController;

class RelationshipFieldtypeController extends CpController
{
    public function index()
    {
        return Resource::collection($this->getIndexItems())->additional(['meta' => [
            'sortColumn' => $this->getSortColumn(),
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

        if ($search = request('search')) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($site = request('site')) {
            $query->where('site', $site);
        }

        return $query;
    }

    public function data(Request $request)
    {
        $items = $this->fieldtype()
            ->getItemData($request->selections, $request->site)
            ->values();

        return Resource::collection($items);
    }

    protected function fieldtype()
    {
        return new Relationship;
    }
}
