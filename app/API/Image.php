<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Image extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Image::class;
    }
}
