<?php

namespace Statamic\Extend;

use ReflectionClass;
use Statamic\API\Str;

trait HasTitleAndHandle
{
    protected static $title;
    protected static $handle;

    public static function title()
    {
        return static::$title ?? Str::humanize(static::handle());
    }

    public static function handle()
    {
        if (static::$handle) {
            return static::$handle;
        }

        $class = (new ReflectionClass(static::class))->getShortName();

        return snake_case($class);
    }
}
