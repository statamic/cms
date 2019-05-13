<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Assets\AssetRepository;

class Asset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AssetRepository::class;
    }
}
