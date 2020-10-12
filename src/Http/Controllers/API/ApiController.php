<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesConditions;

class ApiController extends Controller
{
    use QueriesConditions;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new CpController.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Filter, sort, and paginate query for API resource output.
     *
     * @param \Statamic\Query\Builder $query
     * @return \Statamic\Extensions\Pagination\LengthAwarePaginator
     */
    protected function filterSortAndPaginate($query)
    {
        return $this
            ->filter($query)
            ->sort($query)
            ->paginate($query);
    }

    /**
     * Filters a query based on conditions in the filter parameter.
     *
     * /endpoint?filter[field:condition]=foo&filter[anotherfield]=bar
     *
     * @param \Statamic\Query\Builder $query
     * @return $this
     */
    protected function filter($query)
    {
        collect($this->request->filter ?? [])
            ->each(function ($value, $filter) use ($query) {
                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }

                if (Str::contains($filter, ':')) {
                    [$field, $condition] = explode(':', $filter);
                } else {
                    $field = $filter;
                    $condition = 'equals';
                }

                $this->queryCondition($query, $field, $condition, $value);
            });

        return $this;
    }

    /**
     * Sorts the query based on the sort parameter.
     *
     * Fields can be prexied with a hyphen to sort descending.
     *
     * /endpoint?sort=field
     * /endpoint?sort=field,anotherfield
     * /endpoint?sort=-field
     *
     * @return $this
     */
    protected function sort($query)
    {
        if (! $sorts = $this->request->sort) {
            return $this;
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

        return $this;
    }

    /**
     * Gets a paginator, limited if requested by the limit paramter.
     *
     * /endpoint?limit=10
     *
     * @param \Statamic\Query\Builder $query
     * @return \Statamic\Extensions\Pagination\LengthAwarePaginator
     */
    protected function paginate($query)
    {
        $columns = explode(',', $this->request->input('fields', '*'));

        return $query
            ->paginate($this->request->input('limit', 25), $columns)
            ->appends($this->request->only(['filter', 'limit', 'page']));
    }
}
