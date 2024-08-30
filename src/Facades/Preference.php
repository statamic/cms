<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Preferences\Preferences;

/**
 * @method static \Statamic\Preferences\DefaultPreferences default()
 * @method static void preventMergingChildren(string $dottedKey)
 * @method static array all()
 * @method static mixed get(string $key, $fallback = null)
 * @method static extend(\Closure $callback)
 * @method static void boot()
 * @method static \Statamic\Preferences\Preference register(string $handle, array $field = [])
 * @method static \Statamic\Preferences\Preference make(string $handle, array $field = [])
 * @method static \Illuminate\Support\Collection tabs()
 * @method static void tab()
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
