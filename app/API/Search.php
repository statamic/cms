<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Search extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Search::class;
    }
}
