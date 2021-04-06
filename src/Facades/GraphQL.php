<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\GraphQL\Manager;

class GraphQL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
