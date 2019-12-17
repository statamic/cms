<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\API\TermResource;

class TaxonomyTermsController extends CpController
{
    use TemporaryResourcePagination;

    public function index($collection, Request $request)
    {
        $terms = static::paginate(collect());

        return app(TermResource::class)::collection($terms);
    }
}
