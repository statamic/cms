<?php

namespace Statamic\Filters;

use Statamic\API\Str;

abstract class Filter
{
    public static function title()
    {
        return static::$title
            ?? Str::humanize(str_replace((new \ReflectionClass(static::class))->getNamespaceName().'\\', '', static::class));
    }

    public static function handle()
    {
        return static::$handle ?? snake_case(static::title());
    }

    public function required()
    {
        return false;
    }
}
