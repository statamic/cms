<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\CP\Column;
use Statamic\API\Blueprint;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\API\Preference;
use Statamic\Fields\Validation;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Http\Requests\FilteredSiteRequest;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class TermsController extends CpController
{
    public function index(FilteredSiteRequest $request, $taxonomy)
    {
        $query = $this->indexQuery($taxonomy);

        $this->filter($query, $request->filters);

        $sortField = request('sort');
        $sortDirection = request('order');

        if (!$sortField && !request('search')) {
            $sortField = $taxonomy->sortField();
            $sortDirection = $taxonomy->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $paginator = $query->paginate(request('perPage'));

        $paginator->supplement(function ($term) {
            return ['deleteable' => me()->can('delete', $term)];
        })->preProcessForIndex();

        $columns = $taxonomy->termBlueprint()
            ->columns()
            ->setPreferred("taxonomies.{$taxonomy->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        return Resource::collection($paginator)->additional(['meta' => [
            'filters' => $request->filters,
            'sortColumn' => $sortField,
            'columns' => $columns,
        ]]);
    }

    protected function filter($query, $filters)
    {
        foreach ($filters as $handle => $values) {
            $class = app('statamic.scopes')->get($handle);
            $filter = app($class);
            $filter->apply($query, $values);
        }
    }

    protected function indexQuery($taxonomy)
    {
        $query = $taxonomy->queryTerms();

        if ($search = request('search')) {
            if ($taxonomy->hasSearchIndex()) {
                return $taxonomy->searchIndex()->ensureExists()->search($search);
            }

            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query;
    }

    public function edit(Request $request, $taxonomy, $term)
    {
        //
    }
}
