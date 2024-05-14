<?php

namespace Statamic\Extend;

use ReflectionClass;
use Statamic\Support\Str;

trait HasHandle
{
    protected static $handle;

    public static function handle()
    {
        if (static::$handle) {
            return static::$handle;
        }

        $class = (new ReflectionClass(static::class))->getShortName();

        return Str::snake($class);
    }
}
