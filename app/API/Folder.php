<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Folder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Folder::class;
    }
}
