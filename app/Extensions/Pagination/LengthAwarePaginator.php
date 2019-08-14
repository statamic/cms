<?php

namespace Statamic\Extensions\Pagination;

use Illuminate\Pagination\LengthAwarePaginator as BasePaginator;

class LengthAwarePaginator extends BasePaginator
{
    /**
     * Render the paginator as an array
     *
     * @return array
     */
    public function renderArray()
    {
        return (new Presenter($this))->render();
    }

    /**
     * Add a new key to each item of the collection
     *
     * @param string|callable $key       New key to add, or a function to return an array of new values
     * @param mixed           $callable  Function to return the new value when specifying a key
     * @return $this
     */
    public function supplement($key, $callable = null)
    {
        $this->forwardCallTo($this->getCollection(), 'supplement', [$key, $callable]);

        return $this;
    }
}
