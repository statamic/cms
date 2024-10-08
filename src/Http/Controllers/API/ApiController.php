<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\API\ResourceAuthorizer;
use Statamic\Exceptions\ApiValidationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesConditions;

class ApiController extends Controller
{
    use QueriesConditions;

    protected $resourceConfigKey;
    protected $routeResourceKey;
    protected $filterPublished = false;

    /**
     * Abort if item is unpublished.
     *
     * @param  mixed  $item
     */
    protected function abortIfUnpublished($item)
    {
        if (request()->isLivePreview()) {
            return;
        }

        throw_if($item->published() === false, new NotFoundHttpException);
    }

    /**
     * Abort if endpoint is disabled.
     */
    protected function abortIfDisabled()
    {
        if (! $this->resourceConfigKey) {
            return;
        }

        if (! ResourceAuthorizer::isAllowed('api', $this->resourceConfigKey)) {
            throw new NotFoundHttpException;
        }

        if (! $this->routeResourceKey) {
            return;
        }

        if (! $handle = request()->route($this->routeResourceKey)) {
            return;
        }

        if (! is_string($handle)) {
            $handle = $handle->handle();
        }

        if (! in_array($handle, ResourceAuthorizer::allowedSubResources('api', $this->resourceConfigKey))) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * If endpoint config is an array, filter allowed resources.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @return \Illuminate\Support\Collection
     */
    protected function filterAllowedResources($items)
    {
        $allowedResources = ResourceAuthorizer::allowedSubResources('api', $this->resourceConfigKey);

        return $items->filter(fn ($item) => in_array($item->handle(), $allowedResources));
    }

    /**
     * Filter, sort, and paginate query for API resource output.
     *
     * @param  \Statamic\Query\Builder  $query
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
     * @param  \Statamic\Query\Builder  $query
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
                } elseif (is_numeric($value)) {
                    $value = Str::contains($value, '.') ? (float) $value : (int) $value;
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
        if (! method_exists($this, 'allowedFilters')) {
            return collect();
        }

        $filters = collect(request()->filter ?? []);

        $allowedFilters = collect($this->allowedFilters());

        $forbidden = $filters
            ->keys()
            ->map(fn ($param) => explode(':', $param))
            ->map(fn ($parts) => $parts[0] === 'taxonomy' ? $parts[0].':'.$parts[1] : $parts[0])
            ->filter(fn ($field) => ! $allowedFilters->contains($field));

        if ($forbidden->isNotEmpty()) {
            throw ApiValidationException::withMessages([
                'filter' => Str::plural('Forbidden filter', $forbidden).': '.$forbidden->join(', '),
            ]);
        }

        if ($this->filterPublished && $this->doesntHaveFilter('status') && $this->doesntHaveFilter('published')) {
            $filters->put('status:is', 'published');
        }

        return $filters;
    }

    /**
     * Check if user is not filtering by a specific field, for applying default filters.
     *
     * @param  string  $field
     * @return bool
     */
    protected function doesntHaveFilter($field)
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
     * @param  \Statamic\Query\Builder  $query
     * @return \Statamic\Extensions\Pagination\LengthAwarePaginator
     */
    protected function paginate($query)
    {
        $columns = explode(',', request()->input('fields', '*'));

        return $query
            ->paginate(request()->input('limit', config('statamic.api.pagination_size')), $columns)
            ->appends(request()->only(['filter', 'limit', 'page', 'sort']));
    }

    /**
     * Get query param.
     *
     * @param  string  $key
     * @param  mixed  $default
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
