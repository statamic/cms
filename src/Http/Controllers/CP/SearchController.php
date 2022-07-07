<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Contracts\Search\Searchable;
use Statamic\Facades\Search;
use Statamic\Facades\User;

class SearchController extends CpController
{
    public function __invoke(Request $request)
    {
        return Search::index()
            ->ensureExists()
            ->search($request->query('q'))
            ->get()
            ->filter(function ($item) {
                return User::current()->can('view', $item);
            })
            ->take(10)
            ->map(function (Searchable $item) {
                return [
                    'reference' => $item->getSearchReference(),
                    'title' => $item->getCpSearchResultTitle(),
                    'url' => $item->getCpSearchResultUrl(),
                    'badge' => $item->getCpSearchResultBadge(),
                ];
            });
    }
}
