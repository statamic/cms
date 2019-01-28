<?php

namespace Statamic\Filters;

use Statamic\API;
use Statamic\Filters\Filter;

class Site extends Filter
{
    public function options()
    {
        return API\Site::all()->mapWithKeys(function ($site) {
            return [$site->handle() => $site->name()];
        })->all();
    }

    public function apply($query, $value)
    {
        $query->where('site', $value);
    }

    public function required()
    {
        return true;
    }
}
