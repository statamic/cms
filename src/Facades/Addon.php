<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Extend\AddonRepository;

class Addon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AddonRepository::class;
    }
}
