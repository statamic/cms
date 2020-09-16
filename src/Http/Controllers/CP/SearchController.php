<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Search;

class SearchController extends CpController
{
    public function __invoke(Request $request)
    {
        return Search::index()
            ->ensureExists()
            ->search($request->query('q'))
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return $item->toAugmentedCollection([
                    'title', 'edit_url',
                    'collection', 'is_entry',
                    'taxonomy', 'is_term',
                    'container', 'is_asset',
                ])->withShallowNesting();
            });
    }
}
