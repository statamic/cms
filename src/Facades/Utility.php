<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Utilities\UtilityRepository;

class Utility extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UtilityRepository::class;
    }
}
