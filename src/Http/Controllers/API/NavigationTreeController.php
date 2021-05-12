<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Nav;
use Statamic\Http\Resources\API\TreeResource;

class NavigationTreeController extends ApiController
{
    protected $resourceConfigKey = 'navs';
    protected $routeResourceKey = 'nav';

    public function show($handle)
    {
        $this->abortIfDisabled();

        return app(TreeResource::class)::make($this->getNavTree($handle))
            ->fields($this->queryParam('fields'))
            ->maxDepth($this->queryParam('max_depth'));
    }

    private function getNavTree($handle)
    {
        $nav = Nav::find($handle);

        throw_unless($nav, new NotFoundHttpException("Navigation [{$handle}] not found"));

        $site = $this->queryParam('site');
        $tree = $nav->in($site);

        throw_unless($tree, new NotFoundHttpException("Navigation [{$handle}] not found in [{$site}] site"));

        return $tree;
    }
}
