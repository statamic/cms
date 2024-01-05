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

        $reflection = (new ReflectionClass(static::class));

        $class = $reflection->getShortName();

        if (app()->environment() !== 'testing') {
            if (! Str::startsWith($reflection->getNamespaceName(), 'Statamic\\')) {
                $class = $reflection->getName();
            }
        }

        return snake_case($class);
    }
}
