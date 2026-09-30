<?php

namespace Statamic\Extend;

trait RegistersItself
{
    public static function register(?string $namespace = null)
    {
        $key = self::class;
        $prefix = $namespace ? $namespace.'::' : '';
        $extensions = app('statamic.extensions');

        $extensions[$key] = with($extensions[$key] ?? collect(), function ($bindings) use ($prefix) {
            $bindings[$prefix.static::handle()] = static::class;

            if (method_exists(static::class, 'aliases')) {
                foreach (static::aliases() as $alias) {
                    $bindings[$prefix.$alias] = static::class;
                }
            }

            return $bindings;
        });
    }
}
