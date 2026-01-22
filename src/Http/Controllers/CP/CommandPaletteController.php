<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\CommandPalette\Category;
use Statamic\CommandPalette\ContentSearchResult;
use Statamic\Contracts\Search\Result;
use Statamic\Facades\CommandPalette;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Search\Index;
use Statamic\Support\Arr;

class CommandPaletteController extends CpController
{
    public function index()
    {
        return CommandPalette::build();
    }

    public function search(Request $request)
    {
        $index = Search::index(index: 'cp', locale: Site::selected()->handle());

        return $index
            ->ensureExists()
            ->search($request->query('q'))
            ->get()
            ->filter(function (Result $item) {
                return ! empty($item->getCpUrl()) && User::current()->can('view', $item->getSearchable());
            })
            ->take(10)
            ->map(function (Result $result) use ($index) {
                return (new ContentSearchResult(text: $result->getCpTitle(), category: Category::Search))
                    ->url($result->getCpUrl())
                    ->badge($this->badge($index, $result))
                    ->reference($result->getReference())
                    ->icon($result->getCpIcon())
                    ->toArray();
            })
            ->values();
    }

    private function badge(Index $index, Result $result)
    {
        $badge = $result->getCpBadge();

        if (! Arr::has($index->config(), 'sites')) {
            $badge = $result->getSearchable()->site()->name().' - '.$badge;
        }

        return $badge;
    }
}
