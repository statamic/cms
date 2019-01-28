<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Filters\FilterRepository;

class Filter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FilterRepository::class;
    }
}
