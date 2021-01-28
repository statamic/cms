<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\AssetResource;

class AssetsController extends ApiController
{
    public function index($assetContainer)
    {
        return $this->withCache(function () use ($assetContainer) {
            return app(AssetResource::class)::collection(
                $this->filterSortAndPaginate($assetContainer->queryAssets())
            );
        });
    }

    public function show($assetContainer, $asset)
    {
        return $this->withCache(function () use ($asset) {
            return app(AssetResource::class)::make($asset);
        });
    }
}
