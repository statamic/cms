<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Search;
use Statamic\Facades\Content;
use Illuminate\Http\Request;
use Statamic\Search\IndexNotFoundException;

class SearchController extends CpController
{
    public function __invoke(Request $request)
    {
        return Search::index()
            ->ensureExists()
            ->search($request->query('q'))
            ->limit(10)
            ->get()
            ->toAugmentedCollection([
                'title',
                'collection', 'is_entry',
                'container', 'is_asset',
            ]);
    }
}
