<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\AssetContainer;
use Statamic\Http\Resources\API\AssetContainerResource;

class AssetContainersController extends ApiController
{
    protected $resourceConfigKey = 'assets';
    protected $routeResourceKey = 'asset_container';

    public function index()
    {
        $this->abortIfDisabled();

        return app(AssetContainerResource::class)::collection(
            $this->filterAllowedResources(AssetContainer::all())->values()
        );
    }

    public function show($assetContainer)
    {
        $this->abortIfDisabled();

        return app(AssetContainerResource::class)::make($assetContainer);
    }
}
