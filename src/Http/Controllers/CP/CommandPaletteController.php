<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\CommandPalette\Category;
use Statamic\CommandPalette\ContentSearchResult;
use Statamic\Contracts\Search\Result;
use Statamic\Facades\CommandPalette;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Search;
use Statamic\Facades\User;

class CommandPaletteController extends CpController
{
    public function index()
    {
        // TODO:
        // - Cache nav and/or built command palette?
        // - Bust cache when nav preferences saved?

        Nav::build(commands: true);

        return CommandPalette::build();
    }

    public function search(Request $request)
    {
        return Search::index()
            ->ensureExists()
            ->search($request->query('q'))
            ->get()
            ->filter(function (Result $item) {
                return ! empty($item->getCpUrl()) && User::current()->can('view', $item->getSearchable());
            })
            ->take(10)
            ->map(function (Result $result) {
                return (new ContentSearchResult(text: $result->getCpTitle(), category: Category::Search))
                    ->url($result->getCpUrl())
                    ->badge($result->getCpBadge())
                    ->reference($result->getReference())
                    // ->icon() // TODO: Make dynamic for entries/terms/users?
                    ->toArray();
            })
            ->values();
    }
}
