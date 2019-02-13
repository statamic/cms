<?php

namespace Statamic\Extend;

use Statamic\API\Str;

trait RegistersItself
{
    public static function register()
    {
        return app('statamic.'.static::$binding)[static::handle()] = static::class;
    }
}
