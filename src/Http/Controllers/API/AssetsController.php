<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Facades\Asset;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\API\AssetResource;

class AssetsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $assets = static::paginate(Asset::all());

        return app(AssetResource::class)::collection($assets);
    }
}
