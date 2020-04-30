<?php

namespace Statamic\Modifiers;

use Statamic\Extend\HasHandle;
use Statamic\Support\Str;

/**
 * Modify values within templates.
 */
class Modifier
{
    use HasHandle;

    protected static $binding = 'modifiers';

    public static function register()
    {
        // Not using RegistersItself trait because modifiers bind with camel cased keys, not snake case.
        return app('statamic.'.static::$binding)[Str::camel(static::handle())] = static::class;
    }
}
