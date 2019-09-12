<?php

namespace Statamic\Facades;

use Statamic\Preferences\Preferences;
use Illuminate\Support\Facades\Facade;

class Preference extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Preferences::class;
    }
}
