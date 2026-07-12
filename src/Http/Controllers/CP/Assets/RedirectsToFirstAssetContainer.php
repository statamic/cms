<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\CP\Navigation\NavItem;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\User;

trait RedirectsToFirstAssetContainer
{
    public function redirectToFirstContainer()
    {
        $firstContainerInNav = Nav::build()->pluck('items')->flatten()
            ->filter(fn (NavItem $navItem) => $navItem->url() === cp_route('assets.index'))
            ->map(fn (NavItem $navItem) => $navItem->resolveChildren()->children()?->first())
            ->first();

        if ($firstContainerInNav) {
            abort(redirect($firstContainerInNav->url()));
        }

        $containers = AssetContainer::all()->sortBy->title()->filter(function ($container) {
            return User::current()->can('view', $container);
        });

        if ($containers->isEmpty()) {
            return;
        }

        abort(redirect($containers->first()->showUrl()));
    }
}
