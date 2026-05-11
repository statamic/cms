<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Collection;
use Statamic\Facades\Term;
use Statamic\Http\Resources\API\EntryResource;

class TaxonomyTermEntriesController extends ApiController
{
    protected $filterPublished = true;
    protected $allowedCollections;

    protected function abortIfDisabled()
    {
        // Abort if `taxonomies` resource is disabled
        $this->resourceConfigKey = 'taxonomies';
        $this->routeResourceKey = 'taxonomy';
        parent::abortIfDisabled();

        // Abort if `collections` resource is disabled
        $this->resourceConfigKey = 'collections';
        $this->routeResourceKey = false;
        parent::abortIfDisabled();
    }

    public function index($taxonomy, $term)
    {
        $this->abortIfDisabled();

        $term = Term::find($taxonomy.'::'.$term);

        throw_unless($term, new NotFoundHttpException);

        $query = $term->queryEntries();

        $this->allowedCollections = $this->allowedCollections();

        foreach ($this->allowedCollections as $collection) {
            $query->where('collection', $collection);
        }

        $with = $this->getRelationshipFieldsFromCollections($taxonomy);

        return app(EntryResource::class)::collection(
            $this->updateAndPaginate($query->with($with))
        );
    }

    private function getRelationshipFieldsFromCollections($taxonomy)
    {
        $collections = ($allowed = $this->allowedCollections())
            ? collect($allowed)->map(fn ($collection) => Collection::findByHandle($collection))
            : $taxonomy->collections();

        return $collections->flatMap(function ($collection) {
            return $collection->entryBlueprints()
                ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
                ->filter->isRelationship()->keys()->all();
        })->all();
    }

    private function allowedCollections()
    {
        return ResourceAuthorizer::allowedSubResources('api', 'collections');
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', 'collections', $this->allowedCollections);
    }

    protected function allowedQueryScopes()
    {
        return QueryScopeAuthorizer::allowedForSubResources('api', 'collections', $this->allowedCollections);
    }
}
