<?php

namespace Statamic\Http\Controllers\CP\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Marketplace\AddonsQuery;

class AddonsController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('configure addons');

        $withInstalled = $request->installed ?? false;

        $addons = (new AddonsQuery)
            ->search($request->q)
            ->page($request->page)
            ->installed($withInstalled)
            ->paginate();

        $resource = JsonResource::collection($addons);

        if ($withInstalled) {
            $resource->additional(['unlisted' => $this->unlisted()]);
        }

        return $resource;
    }

    protected function unlisted()
    {
        return Addon::all()->reject->existsOnMarketplace()->map(function ($addon) {
            return [
                'name' => $addon->name(),
                'package' => $addon->package(),
            ];
        })->values()->all();
    }
}
