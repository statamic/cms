<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use Statamic\Http\Resources\API\TreeResource;

class CollectionTreeController extends ApiController
{
    public function show($collection)
    {
        $site = request('site', Site::default()->handle());

        $tree = $collection->structure()->in($site);

        throw_unless($tree, new NotFoundHttpException("Collection [{$collection->handle()}] not found in [{$site}] site"));

        $fields = explode(',', request('fields', '*'));
        $maxDepth = request('max_depth');

        return app(TreeResource::class)::make($tree)
            ->fields($fields)
            ->maxDepth($maxDepth);
    }
}
