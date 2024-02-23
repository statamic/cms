<?php

namespace Statamic\Extend;

use Closure;
use Illuminate\Pipeline\Pipeline;

trait HasHooks
{
    public static $hooks = [];

    public static function hook(string $name, Closure $hook)
    {
        static::$hooks[static::class][$name][] = $hook;
    }

    protected function runHooks(string $name, $payload = null)
    {
        $closures = collect(
            static::$hooks[static::class][$name] ?? []
        )->map->bindTo($this, $this);

        return (new Pipeline)
            ->send($payload)
            ->through($closures->all())
            ->thenReturn();
    }
}
