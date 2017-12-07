<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class AssetContainer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\AssetContainer::class;
    }
}
