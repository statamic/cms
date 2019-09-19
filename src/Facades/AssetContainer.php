<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Assets\AssetContainerRepository;

class AssetContainer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AssetContainerRepository::class;
    }
}
