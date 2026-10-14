<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Site;
use Statamic\Http\Resources\API\SiteResource;

class SitesController extends ApiController
{
    protected $resourceConfigKey = 'sites';

    public function index()
    {
        $this->abortIfDisabled();

        return app(SiteResource::class)::collection(Site::all()->values());
    }
}
