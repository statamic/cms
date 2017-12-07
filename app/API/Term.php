<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Term extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Term::class;
    }
}
