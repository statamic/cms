<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\TermResource;

class TaxonomyTermsController extends ApiController
{
    public function index($taxonomy)
    {
        return app(TermResource::class)::collection(
            $this->filterSortAndPaginate($taxonomy->queryTerms())
        );
    }

    public function show($collection, $term)
    {
        return app(TermResource::class)::make($term);
    }
}
