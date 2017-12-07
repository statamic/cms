<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Content extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Content::class;
    }
}
