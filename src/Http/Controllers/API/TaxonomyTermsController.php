<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Term;
use Statamic\Http\Resources\API\TermResource;

class TaxonomyTermsController extends ApiController
{
    protected $resourceConfigKey = 'taxonomies';
    protected $routeResourceKey = 'taxonomy';
    protected $taxonomyHandle;

    public function index($taxonomy)
    {
        $this->abortIfDisabled();

        $this->taxonomyHandle = $taxonomy->handle();

        $with = $taxonomy->termBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(TermResource::class)::collection(
            $this->updateAndPaginate($taxonomy->queryTerms()->with($with))
        );
    }

    public function show($taxonomy, $term)
    {
        $this->abortIfDisabled();

        $term = Term::find($taxonomy.'::'.$term);

        throw_unless($term, new NotFoundHttpException);

        return app(TermResource::class)::make($term);
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', 'taxonomies', $this->taxonomyHandle);
    }

    protected function allowedQueryScopes()
    {
        return QueryScopeAuthorizer::allowedForSubResources('api', 'taxonomies', $this->taxonomyHandle);
    }
}
