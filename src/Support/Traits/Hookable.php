<?php

namespace Statamic\Support\Traits;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Statamic\Hooks\Payload;

trait Hookable
{
    public static function hook(string $name, Closure $hook)
    {
        $hooks = app('statamic.hooks');

        if (! isset($hooks[static::class])) {
            $hooks[static::class] = collect();
        }

        if (! isset($hooks[static::class][$name])) {
            $hooks[static::class][$name] = collect();
        }

        $hooks[static::class][$name][] = $hook;
    }

    protected function runHooks(string $name, $payload = null)
    {
        $closures = collect(
            app('statamic.hooks')[static::class][$name] ?? []
        )->map->bindTo($this, $this);

        if (debugbar()->isEnabled()) {
            $message = vsprintf('Hook: %s (Listeners: %s)', [
                static::class.'@'.$name,
                $closures->count(),
            ]);

            debugbar()->addMessage($message, 'hooks');
        }

        if ($closures->isEmpty()) {
            return $payload;
        }

        return (new Pipeline)
            ->send($payload)
            ->through($closures->all())
            ->thenReturn();
    }

    protected function runHooksWith(string $name, array $payload)
    {
        return $this->runHooks($name, new Payload($payload));
    }
}
