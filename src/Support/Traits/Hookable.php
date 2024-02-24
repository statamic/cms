<?php

namespace Statamic\Support\Traits;

use Closure;
use Illuminate\Pipeline\Pipeline;

trait Hookable
{
    private static $hooks = [];

    public static function hook(string $name, Closure $hook)
    {
        static::$hooks[static::class][$name][] = $hook;
    }

    protected function runHooks(string $name, $payload = null)
    {
        $closures = collect(
            static::$hooks[static::class][$name] ?? []
        )->map->bindTo($this, $this);

        if (debugbar()->isEnabled()) {
            $message = vsprintf('Hook: %s (Listeners: %s)', [
                static::class.'@'.$name,
                $closures->count(),
            ]);

            debugbar()->addMessage($message, 'hooks');
        }

        return (new Pipeline)
            ->send($payload)
            ->through($closures->all())
            ->thenReturn();
    }
}
