<?php

namespace Statamic\Extend;

trait HasAliases
{
    protected static $aliases = [];

    public static function aliases()
    {
        return static::$aliases;
    }
}
