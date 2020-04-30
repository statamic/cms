<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Preferences\Preferences;

class Preference extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Preferences::class;
    }
}
