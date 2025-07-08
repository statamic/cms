<?php

namespace Statamic\Http\Controllers\CP\Addons;

use Statamic\CP\Column;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;

class AddonsController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure addons');
    }

    public function index()
    {
        return view('statamic::addons.index', [
            'addons' => Addon::all()->map(fn ($addon) => [
                'name' => $addon->name(),
                'version' => $addon->version(),
                'developer' => $addon->developer() ?? $addon->marketplaceSellerSlug(),
                'description' => $addon->description(),
                'marketplace_url' => $addon->marketplaceUrl(),
                'updates_url' => $addon->updatesUrl(),
                'settings_url' => $addon->settingsUrl(),
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
