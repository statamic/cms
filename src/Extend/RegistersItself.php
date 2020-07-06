<?php

namespace Statamic\Extend;

trait RegistersItself
{
    public static function register()
    {
        $key = self::class;
        $extensions = app('statamic.extensions');

        $extensions[$key] = with($extensions[$key] ?? collect(), function ($bindings) {
            $bindings[static::handle()] = static::class;

            if (method_exists(static::class, 'aliases')) {
                foreach (static::aliases() as $alias) {
                    $bindings[$alias] = static::class;
                }
            }

            return $bindings;
        });
    }
}
