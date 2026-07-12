<?php

namespace Statamic\Extend;

use Statamic\Support\Str;

use function Statamic\trans as __;

trait HasTitle
{
    protected static $title;

    public static function title()
    {
        return __(static::$title ?? Str::title(Str::humanize(static::handle())));
    }
}
