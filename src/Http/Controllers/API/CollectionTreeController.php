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
        $fields = explode(',', request('fields', '*'));

        $tree = $collection->structure()->in($site);

        throw_unless($tree, new NotFoundHttpException("Collection [{$collection->handle()}] not found in [{$site}] site"));

        return app(TreeResource::class)::make($tree)->fields($fields);
    }
}
