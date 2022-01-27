<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Resources\API\TreeResource;
use Statamic\Query\ItemQueryBuilder;

class CollectionTreeController extends ApiController
{
    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';
    protected $filterPublished = true;

    public function show($collection)
    {
        $this->abortIfDisabled();

        $site = $this->queryParam('site');

        $query = new ItemQueryBuilder();
        $this->filter($query);

        return app(TreeResource::class)::make($this->getCollectionTree($collection, $site))
            ->query($query)
            ->fields($this->queryParam('fields'))
            ->maxDepth($this->queryParam('max_depth'))
            ->site($site);
    }

    private function getCollectionTree($collection, $site)
    {
        $structure = $collection->structure();

        throw_unless($structure, new NotFoundHttpException("Collection [{$collection->handle()}] is not a structured collection"));

        $tree = $structure->in($site);

        throw_unless($tree, new NotFoundHttpException("Collection [{$collection->handle()}] not found in [{$site}] site"));

        return $tree;
    }
}
