<?php

namespace Statamic\Extensions\Pagination;

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
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! in_array($method, static::CHAINABLE_METHODS)) {
            return parent::__call($method, $parameters);
        }

        $this->forwardCallTo($this->getCollection(), $method, $parameters);

        return $this;
    }
}
