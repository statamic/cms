<?php

namespace Statamic\Http\Controllers\CP\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Marketplace\AddonsQuery;

class AddonsController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('configure addons');

        $addons = (new AddonsQuery)
            ->search($request->q)
            ->page($request->page)
            ->installed($request->installed ?? false)
            ->paginate();

        return JsonResource::collection($addons);
    }
}
