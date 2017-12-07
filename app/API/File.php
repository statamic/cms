<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class File extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\File::class;
    }
}
