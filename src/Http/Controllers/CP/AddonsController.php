<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\CP\Column;
use Statamic\Facades\Addon;

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
                'updates_url' => $addon->marketplaceSlug() ? cp_route('updater.product', $addon->marketplaceSlug()) : null,
            ])->all(),
            'columns' => [
                Column::make('name'),
                Column::make('developer'),
                Column::make('description'),
                Column::make('version'),
            ],
        ]);
    }
}
