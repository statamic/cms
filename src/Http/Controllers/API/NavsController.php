<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Nav;
use Statamic\Http\Resources\API\NavResource;

class NavsController extends ApiController
{
    protected $resourceConfigKey = 'navs';
    protected $routeResourceKey = 'nav';

    public function index()
    {
        $this->abortIfDisabled();

        return app(NavResource::class)::collection(
            $this->filterAllowedResources(Nav::all())->values()
        );
    }

    public function show($handle)
    {
        $this->abortIfDisabled();

        throw_unless($nav = Nav::find($handle), new NotFoundHttpException("Navigation [{$handle}] not found"));

        return app(NavResource::class)::make($nav);
    }
}
