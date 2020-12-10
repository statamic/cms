<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Http\Resources\API\TreeResource;

class NavigationController extends ApiController
{
    public function show($navHandle)
    {
        $nav = Nav::find($navHandle);

        throw_unless($nav, new NotFoundHttpException("Navigation [{$navHandle}] not found"));

        $site = request('site', Site::default()->handle());
        $fields = explode(',', request('fields', '*'));

        $tree = $nav->in($site);

        throw_unless($tree, new NotFoundHttpException("Navigation [{$navHandle}] not found in [{$site}] site"));

        return app(TreeResource::class)::make($tree)->fields($fields);
    }
}
