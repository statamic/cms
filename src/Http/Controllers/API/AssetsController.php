<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\AssetResource;

class AssetsController extends ApiController
{
    protected $endpointConfigKey = 'assets';
    protected $limitRouteResource = 'asset_container';

    public function index($assetContainer)
    {
        $this->abortIfDisabled();

        return app(AssetResource::class)::collection(
            $this->filterSortAndPaginate($assetContainer->queryAssets())
        );
    }

    public function show($assetContainer, $asset)
    {
        $this->abortIfDisabled();

        return app(AssetResource::class)::make($asset);
    }
}
