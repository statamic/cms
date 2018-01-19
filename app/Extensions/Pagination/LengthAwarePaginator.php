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
}