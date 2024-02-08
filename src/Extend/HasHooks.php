<?php

namespace Statamic\Extend;

use Closure;

trait HasHooks
{
    protected static $setupHooks = [];

    /**
     * Add a hook into the setup point of the lifecycle
     *
     * @return void
     */
    public static function addSetupHook(Closure $hook)
    {
        $handle = self::handle();

        if (! isset(self::$setupHooks[$handle])) {
            self::$setupHooks[$handle] = [];
        }

        self::$setupHooks[$handle][] = $hook;
    }

    /**
     * Run any setup hooks registered
     *
     * @return void
     */
    protected function runSetupHooks()
    {
        foreach ((static::$setupHooks[self::handle()] ?? []) as $hook) {
            $hook($this);
        }
    }
}
