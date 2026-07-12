<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\GlobalSet;
use Statamic\Http\Resources\API\GlobalSetResource;

class GlobalsController extends ApiController
{
    protected $resourceConfigKey = 'globals';
    protected $routeResourceKey = 'global';

    public function index()
    {
        $this->abortIfDisabled();

        return app(GlobalSetResource::class)::collection(
            $this->filterAllowedResources(GlobalSet::all()->map->in($this->queryParam('site')))
        );
    }

    public function show($variables)
    {
        $this->abortIfDisabled();

        $localized = $variables->globalSet()->in($this->queryParam('site'));

        return app(GlobalSetResource::class)::make($localized);
    }
}
