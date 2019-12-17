<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Asset;
use Illuminate\Http\Request;
use Statamic\Http\Resources\AssetResource;
use Statamic\Http\Controllers\CP\CpController;

class AssetsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $assets = static::paginate(Asset::all());

        return app(AssetResource::class)::collection($assets);
    }
}
