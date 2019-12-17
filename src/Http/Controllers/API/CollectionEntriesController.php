<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Support\Str;
use Statamic\Tags\Query\HasConditions;

class CollectionEntriesController extends CpController
{
    use HasConditions;

    public function index($collection, Request $request)
    {
        $query = $collection->queryEntries();

        $this->filter($query, $request);
        $this->sort($query, $request);
        $paginator = $this->paginate($query, $request);

        return app(EntryResource::class)::collection($paginator);
    }

    public function show($collection, $entry)
    {
        return app(EntryResource::class)::make($entry);
    }

    /**
     * Filters a query based on conditions in the filter parameter.
     *
     * /endpoint?filter[field:condition]=foo&filter[anotherfield]=bar
     */
    protected function filter($query, $request)
    {
        collect($request->filter ?? [])
            ->each(function ($value, $filter) use ($query) {
                if (Str::contains($filter, ':')) {
                    [$field, $condition] = explode(':', $filter);
                } else {
                    $field = $filter;
                    $condition = 'equals';
                }

                $this->queryCondition($query, $field, $condition, $value);
            });
    }

    /**
     * Gets a paginator, limited if requested by the limit paramter.
     *
     * /endpoint?limit=10
     */
    protected function paginate($query, $request)
    {
        return $query
            ->paginate($request->input('limit', 25))
            ->appends($request->only(['filter', 'limit', 'page']));
    }

    /**
     * Sorts the query based on the sort parameter.
     *
     * Fields can be prexied with a hyphen to sort descending.
     *
     * /endpoint?sort=field
     * /endpoint?sort=field,anotherfield
     * /endpoint?sort=-field
     */
    protected function sort($query, $request)
    {
        if (! $sorts = $request->sort) {
            return;
        }

        collect(explode(',', $sorts))
            ->each(function ($sort) use ($query) {
                $order = 'asc';

                if (Str::startsWith($sort, '-')) {
                    $sort = substr($sort, 1);
                    $order = 'desc';
                }

                $query->orderBy($sort, $order);
            });
    }
}
