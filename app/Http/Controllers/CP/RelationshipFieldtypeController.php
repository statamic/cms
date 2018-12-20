<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;

class RelationshipFieldtypeController extends CpController
{
    public function index()
    {
        $items = Entry::query()
            ->orderBy($sort = request('sort', 'title'), request('order', 'asc'))
            ->paginate();

        return Resource::collection($items)->additional(['meta' => [
            'sortColumn' => $sort,
            'columns' => [
                ['label' => __('Title'), 'field' => 'title'],
                ['label' => __('URL'), 'field' => 'url'],
            ],
        ]]);
    }

    public function data(Request $request)
    {
        $items = collect($request->selections)->map(function ($id) {
            $entry = Entry::find($id);

            return [
                'id' => $id,
                'title' => $entry->get('title'),
            ];
        });

        return Resource::collection($items);
    }
}
