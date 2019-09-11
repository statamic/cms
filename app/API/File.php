<?php

namespace Statamic\API;

use Statamic\Filesystem\Manager;
use Illuminate\Support\Facades\Facade;

class File extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
