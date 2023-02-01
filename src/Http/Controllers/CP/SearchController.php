<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Contracts\Search\Result;
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
            ->filter(function (Result $item) {
                return User::current()->can('view', $item->getSearchable());
            })
            ->take(10)
            ->map(function (Result $result) {
                return [
                    'reference' => $result->getReference(),
                    'title' => $result->getCpTitle(),
                    'url' => $result->getCpUrl(),
                    'badge' => $result->getCpBadge(),
                ];
            });
    }
}
