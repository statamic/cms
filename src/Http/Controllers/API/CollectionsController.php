<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CollectionResource;

class CollectionsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $collections = static::paginate(Collection::all());

        return CollectionResource::collection($collections);
    }
}
