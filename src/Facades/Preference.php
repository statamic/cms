<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Preferences\Preferences;

/**
 * @method static array all()
 * @method static mixed get($key, $fallback = null)
 *
 * @see \Statamic\Preferences\Preferences
 */
class Preference extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Preferences::class;
    }
}
