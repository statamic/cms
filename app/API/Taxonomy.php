<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Taxonomy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Taxonomy::class;
    }
}
