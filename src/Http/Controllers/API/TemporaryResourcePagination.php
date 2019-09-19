<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Pagination\LengthAwarePaginator;

trait TemporaryResourcePagination
{
    /**
     * TODO: Each repository should probably have it's own performant method of pagination?
     */
    public static function paginate($data, $perPage = 30)
    {
        $currentPage = request()->input('page', 1);
        $items = $data->forPage($currentPage, $perPage)->values();
        $total = $data->count();
        $options = ['path' => collect(explode('?', request()->getUri()))->first()];

        return new LengthAwarePaginator($items, $total, $perPage, $currentPage, $options);
    }
}
