<?php

namespace Statamic\Extend;

use Closure;

trait HasHooks
{
    public static $hooks = [];

    public static function addHook(string $name, Closure $hook)
    {
        static::$hooks[static::class][$name][] = $hook;
    }

    protected function runHook(string $name)
    {
        foreach ((static::$hooks[static::class][$name] ?? []) as $hook) {
            call_user_func($hook, $this);
        }
    }
}
