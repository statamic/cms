<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Imaging\Manager;

class Image extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
