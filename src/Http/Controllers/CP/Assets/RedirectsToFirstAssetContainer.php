<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;

trait RedirectsToFirstAssetContainer
{
    public function redirectToFirstContainer()
    {
        $containers = AssetContainer::all()->sortBy->title()->filter(function ($container) {
            return User::current()->can('view', $container);
        });

        if ($containers->isEmpty()) {
            return;
        }

        abort(redirect($containers->first()->showUrl()));
    }
}
