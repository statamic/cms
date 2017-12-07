<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Storage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Storage::class;
    }
}
