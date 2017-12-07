<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Page extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Page::class;
    }
}
