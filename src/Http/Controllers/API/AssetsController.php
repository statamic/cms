<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use Statamic\Http\Resources\API\AssetResource;

class AssetsController extends ApiController
{
    protected $resourceConfigKey = 'assets';
    protected $routeResourceKey = 'asset_container';
    protected $containerHandle;

    public function index($assetContainer)
    {
        $this->abortIfDisabled();

        $this->containerHandle = $assetContainer->handle();

        $with = $assetContainer->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        return app(AssetResource::class)::collection(
            $this->updateAndPaginate($assetContainer->queryAssets()->with($with))
        );
    }

    public function show($assetContainer, $asset)
    {
        $this->abortIfDisabled();

        return app(AssetResource::class)::make($asset);
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', 'assets', $this->containerHandle);
    }

    protected function allowedQueryScopes()
    {
        return QueryScopeAuthorizer::allowedForSubResources('api', 'assets', $this->containerHandle);
    }
}
