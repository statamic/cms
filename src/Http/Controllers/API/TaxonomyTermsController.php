<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Term;
use Statamic\Http\Resources\API\TermResource;

class TaxonomyTermsController extends ApiController
{
    protected $resourceConfigKey = 'taxonomies';
    protected $routeResourceKey = 'taxonomy';

    public function index($taxonomy)
    {
        $this->abortIfDisabled();

        $with = $taxonomy->termBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(TermResource::class)::collection(
            $this->filterSortAndPaginate($taxonomy->queryTerms()->with($with))
        );
    }

    public function show($taxonomy, $term)
    {
        $this->abortIfDisabled();

        $term = Term::find($taxonomy.'::'.$term);

        throw_unless($term, new NotFoundHttpException);

        return app(TermResource::class)::make($term);
    }
}
