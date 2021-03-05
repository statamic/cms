<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\TermResource;

class TaxonomyTermsController extends ApiController
{
    protected $resourceConfigKey = 'taxonomies';
    protected $routeResourceKey = 'taxonomy';

    public function index($taxonomy)
    {
        $this->abortIfDisabled();

        return app(TermResource::class)::collection(
            $this->filterSortAndPaginate($taxonomy->queryTerms())
        );
    }

    public function show($taxonomy, $term)
    {
        $this->abortIfDisabled();

        return app(TermResource::class)::make($term);
    }
}
