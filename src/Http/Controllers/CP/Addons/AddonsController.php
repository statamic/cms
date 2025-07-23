<?php

namespace Statamic\Http\Controllers\CP\Addons;

use Statamic\Addons\Addon;
use Statamic\CP\Column;
use Statamic\Facades;
use Statamic\Http\Controllers\CP\CpController;

class AddonsController extends CpController
{
    public function index()
    {
        $this->authorize('index', Addon::class);

        return view('statamic::addons.index', [
            'addons' => Facades\Addon::all()->map(fn (Addon $addon) => [
                'name' => $addon->name(),
                'version' => $addon->version(),
                'developer' => $addon->developer() ?? $addon->marketplaceSellerSlug(),
                'description' => $addon->description(),
                'marketplace_url' => $addon->marketplaceUrl(),
                'updates_url' => Facades\User::current()->can('view updates') ? $addon->updatesUrl() : null,
                'settings_url' => Facades\User::current()->can('editSettings', $addon) ? $addon->settingsUrl() : null,
            ])->values()->all(),
            'columns' => [
                Column::make('name'),
                Column::make('developer'),
                Column::make('description'),
                Column::make('version'),
            ],
        ]);
    }
}
