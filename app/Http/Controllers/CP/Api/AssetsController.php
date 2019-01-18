<?php

namespace Statamic\Http\Controllers\CP\Api;

use Illuminate\Http\Request;
use Statamic\API\Asset;
use Statamic\Http\Resources\AssetResource;
use Statamic\Http\Controllers\CP\CpController;

class AssetsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $assets = static::paginate(Asset::all());

        return AssetResource::collection($assets);
    }
}
