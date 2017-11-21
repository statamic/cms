<?php

namespace Statamic\API;

use Statamic\Stache\Manager;

class Stache
{
    /**
     * @throws \Exception
     */
    public static function update()
    {
        app(Manager::class)->update();
    }

    /**
     * Clear the Stache
     *
     * @return void
     */
    public static function clear()
    {
        collect(Cache::get('stache::keys', []))
            ->merge(['meta', 'timestamps', 'config', 'keys', 'duplicates'])
            ->each(function ($key) {
                Cache::forget("stache::$key");
            });
    }
}
