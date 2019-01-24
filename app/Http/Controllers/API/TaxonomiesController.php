<?php

namespace Statamic\Http\Controllers\API;

use Statamic\API\Taxonomy;
use Illuminate\Http\Request;
use Statamic\Http\Resources\TaxonomyResource;
use Statamic\Http\Controllers\CP\CpController;

class TaxonomiesController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $taxonomies = static::paginate(collect());

        return TaxonomyResource::collection($taxonomies);
    }
}
