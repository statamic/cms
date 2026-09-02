<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Taxonomy;
use Statamic\Http\Resources\API\TaxonomyResource;

class TaxonomiesController extends ApiController
{
    protected $resourceConfigKey = 'taxonomies';
    protected $routeResourceKey = 'taxonomy';

    public function index()
    {
        $this->abortIfDisabled();

        return app(TaxonomyResource::class)::collection(
            $this->filterAllowedResources(Taxonomy::all())->values()
        );
    }

    public function show($taxonomy)
    {
        $this->abortIfDisabled();

        return app(TaxonomyResource::class)::make($taxonomy);
    }
}
