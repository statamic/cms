<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Resources\API\TreeResource;

class CollectionTreeController extends ApiController
{
    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';

    public function show($collection)
    {
        $this->abortIfDisabled();

        return app(TreeResource::class)::make($this->getCollectionTree($collection))
            ->fields($this->queryParam('fields'))
            ->maxDepth($this->queryParam('max_depth'));
    }

    private function getCollectionTree($collection)
    {
        $structure = $collection->structure();

        throw_unless($structure, new NotFoundHttpException("Collection [{$collection->handle()}] is not a structured collection"));

        $site = $this->queryParam('site');
        $tree = $structure->in($site);

        throw_unless($tree, new NotFoundHttpException("Collection [{$collection->handle()}] not found in [{$site}] site"));

        return $tree;
    }
}
