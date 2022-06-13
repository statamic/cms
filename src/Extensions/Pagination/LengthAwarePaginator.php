<?php

namespace Statamic\Extensions\Pagination;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse;
use Illuminate\Pagination\LengthAwarePaginator as BasePaginator;

class LengthAwarePaginator extends BasePaginator
{
    const CHAINABLE_METHODS = [
        'supplement',
        'preProcessForIndex',
    ];

    /**
     * Render the paginator as an array.
     *
     * @return array
     */
    public function renderArray()
    {
        return (new Presenter($this))->render();
    }

    /**
     * Make dynamic calls into the collection.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->isApiPluckingResource($method, $parameters)) {
            return $this->pluckResourcesForApi();
        }

        if (! in_array($method, static::CHAINABLE_METHODS)) {
            return parent::__call($method, $parameters);
        }

        $this->forwardCallTo($this->getCollection(), $method, $parameters);

        return $this;
    }

    private function isApiPluckingResource($method, $parameters)
    {
        if ($method !== 'pluck' && $parameters !== ['resource']) {
            return false;
        }

        return collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 5))
            ->pluck('class')
            ->contains(PaginatedResourceResponse::class);
    }

    private function pluckResourcesForApi()
    {
        return $this->getCollection()->map(function ($item) {
            return $item->resource;
        });
    }

    public function withQueryString()
    {
        $this->appends(request()->query());

        return $this;
    }
}
