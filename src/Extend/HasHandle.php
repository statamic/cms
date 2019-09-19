<?php

namespace Statamic\Extend;

use ReflectionClass;

trait HasHandle
{
    protected static $handle;

    public static function handle()
    {
        if (static::$handle) {
            return static::$handle;
        }

        $class = (new ReflectionClass(static::class))->getShortName();

        return snake_case($class);
    }
}
