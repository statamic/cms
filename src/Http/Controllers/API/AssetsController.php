<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\AssetResource;

class AssetsController extends ApiController
{
    public $endpointConfigKey = 'assets';

    public function index($assetContainer)
    {
        return app(AssetResource::class)::collection(
            $this->filterSortAndPaginate($assetContainer->queryAssets())
        );
    }

    public function show($assetContainer, $asset)
    {
        return app(AssetResource::class)::make($asset);
    }
}
