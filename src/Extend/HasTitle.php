<?php

namespace Statamic\Extend;

use Statamic\Support\Str;

trait HasTitle
{
    protected static $title;

    public static function title()
    {
        return __(static::$title ?? Str::title(Str::humanize(static::handle())));
    }
}
