<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Crypt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Crypt::class;
    }
}
