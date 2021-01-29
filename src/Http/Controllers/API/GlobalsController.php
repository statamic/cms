<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\GlobalSet;
use Statamic\Http\Resources\API\GlobalSetResource;

class GlobalsController extends ApiController
{
    public function index()
    {
        return app(GlobalSetResource::class)::collection(
            GlobalSet::all()->map->in($this->queryParam('site'))
        );
    }

    public function show($globalSet)
    {
        return app(GlobalSetResource::class)::make($globalSet);
    }
}
