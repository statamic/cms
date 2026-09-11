<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Resources\API\TaxonomyTreeResource;

class TaxonomyTreeController extends ApiController
{
    protected $resourceConfigKey = 'taxonomies';
    protected $routeResourceKey = 'taxonomy';

    public function show($taxonomy)
    {
        $this->abortIfDisabled();

        throw_unless($taxonomy->hasStructure(), new NotFoundHttpException("Taxonomy [{$taxonomy->handle()}] is not a structured taxonomy"));

        return app(TaxonomyTreeResource::class)::make($taxonomy)
            ->fields($this->queryParam('fields'))
            ->maxDepth($this->queryParam('max_depth'))
            ->site($this->queryParam('site'));
    }
}
