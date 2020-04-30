<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Http\Resources\API\TermResource;

class TaxonomyTermsController extends ApiController
{
    public function index($taxonomy, Request $request)
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
