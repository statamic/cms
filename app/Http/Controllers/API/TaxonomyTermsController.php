<?php

namespace Statamic\Http\Controllers\API;

use Statamic\API\Term;
use Illuminate\Http\Request;
use Statamic\Http\Resources\TermResource;
use Statamic\Http\Controllers\CP\CpController;

class TaxonomyTermsController extends CpController
{
    use TemporaryResourcePagination;

    public function index($collection, Request $request)
    {
        $terms = static::paginate(collect());

        return TermResource::collection($terms);
    }
}
