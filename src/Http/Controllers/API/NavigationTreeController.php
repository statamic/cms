<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Nav;
use Statamic\Http\Resources\API\TreeResource;
use Statamic\Structures\PageQueryBuilder;

class NavigationTreeController extends ApiController
{
    protected $resourceConfigKey = 'navs';
    protected $routeResourceKey = 'nav';

    public function show($handle)
    {
        $this->abortIfDisabled();

        $site = $this->queryParam('site');

        $query = new PageQueryBuilder();
        $this->filter($query);

        return app(TreeResource::class)::make($this->getNavTree($handle, $site))
            ->query($query)
            ->fields($this->queryParam('fields'))
            ->maxDepth($this->queryParam('max_depth'))
            ->site($site);
    }

    private function getNavTree($handle, $site)
    {
        $nav = Nav::find($handle);

        throw_unless($nav, new NotFoundHttpException("Navigation [{$handle}] not found"));

        $tree = $nav->in($site);

        throw_unless($tree, new NotFoundHttpException("Navigation [{$handle}] not found in [{$site}] site"));

        return $tree;
    }
}
