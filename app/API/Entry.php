<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Entry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Entry::class;
    }
}
