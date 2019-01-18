<?php

namespace Statamic\Http\Controllers\CP\Api;

use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Http\Resources\CollectionResource;
use Statamic\Http\Controllers\CP\CpController;

class CollectionsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $collections = static::paginate(Collection::all());

        return CollectionResource::collection($collections);
    }
}
