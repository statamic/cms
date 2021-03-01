<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesConditions;

class ApiController extends Controller
{
    use QueriesConditions;

    protected $filterPublished = false;

    /**
     * Abort if item is unpublished.
     *
     * @param mixed $item
     * @return bool
     */
    protected function abortIfUnpublished($item)
    {
        throw_if($item->published() === false, new NotFoundHttpException);
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
        $this->getFilters()
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
     * Get filters for querying.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFilters()
    {
        $filters = collect(request()->filter ?? []);

        if ($this->filterPublished && $this->doesntHaveFilter('status') && $this->doesntHaveFilter('published')) {
            $filters->put('status:is', 'published');
        }

        return $filters;
    }

    /**
     * Check if user is not filtering by a specific field, for applying default filters.
     *
     * @param string $fieldS
     * @return bool
     */
    public function doesntHaveFilter($field)
    {
        return ! collect(request()->filter ?? [])
            ->map(function ($value, $param) {
                return explode(':', $param)[0];
            })
            ->contains($field);
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
        if (! $sorts = request()->sort) {
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
        $columns = explode(',', request()->input('fields', '*'));

        return $query
            ->paginate(request()->input('limit', 25), $columns)
            ->appends(request()->only(['filter', 'limit', 'page']));
    }

    /**
     * Get query param.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function queryParam($key, $default = null)
    {
        if ($key === 'site') {
            return request()->input('site', Site::default()->handle());
        }

        if ($key === 'fields') {
            return explode(',', request()->input($key, '*'));
        }

        return request()->input($key, $default);
    }
}
