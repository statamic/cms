<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Cache;
use Statamic\Stache\Manager;

class Stache
{
    /**
     * @throws \Exception
     */
    public function update()
    {
        app(Manager::class)->update();
    }

    /**
     * Clear the Stache
     *
     * @return void
     */
    public function clear()
    {
        collect(Cache::get('stache::keys', []))
            ->merge(['meta', 'timestamps', 'config', 'keys', 'duplicates'])
            ->each(function ($key) {
                Cache::forget("stache::$key");
            });
    }
}
