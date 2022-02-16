<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\AssetResource;

class AssetsController extends ApiController
{
    protected $resourceConfigKey = 'assets';
    protected $routeResourceKey = 'asset_container';

    public function index($assetContainer)
    {
        $this->abortIfDisabled();

        $with = $assetContainer->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        return app(AssetResource::class)::collection(
            $this->filterSortAndPaginate($assetContainer->queryAssets()->with($with))
        );
    }

    public function show($assetContainer, $asset)
    {
        $this->abortIfDisabled();

        return app(AssetResource::class)::make($asset);
    }
}
