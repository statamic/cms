<?php

namespace Statamic\View\Antlers\Language\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;

class Runtime extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RuntimeConfiguration::class;
    }
}
