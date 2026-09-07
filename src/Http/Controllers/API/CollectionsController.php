<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Collection;
use Statamic\Http\Resources\API\CollectionResource;

class CollectionsController extends ApiController
{
    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';

    public function index()
    {
        $this->abortIfDisabled();

        return app(CollectionResource::class)::collection(
            $this->filterAllowedResources(Collection::all())->values()
        );
    }

    public function show($collection)
    {
        $this->abortIfDisabled();

        return app(CollectionResource::class)::make($collection);
    }
}
