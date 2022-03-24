<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Collection;
use Statamic\Facades\Term;
use Statamic\Http\Resources\API\EntryResource;

class TaxonomyTermEntriesController extends ApiController
{
    protected $filterPublished = true;

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

        $query = $term->queryEntries();

        foreach ($this->allowedCollections() as $collection) {
            $query->where('collection', $collection);
        }

        $with = $this->getRelationshipFieldsFromCollections($taxonomy);

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($query->with($with))
        );
    }

    private function allowedCollections()
    {
        $entriesConfig = config('statamic.api.resources.collections');

        return is_array($entriesConfig) ? $entriesConfig : [];
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
}
