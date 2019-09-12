<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class PageFolder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\PageFolder::class;
    }
}
