<?php

namespace Statamic\Extend;

trait RegistersItself
{
    public static function register()
    {
        return app('statamic.'.static::$binding)[static::handle()] = static::class;
    }
}
