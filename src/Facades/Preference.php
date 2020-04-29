<?php

namespace Statamic\Facades;

use Statamic\Preferences\Preferences;
use Illuminate\Support\Facades\Facade;

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
