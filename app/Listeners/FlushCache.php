<?php

namespace Statamic\Listeners;

use Statamic\API\Cache;
use Statamic\API\Stache;
use Statamic\Events\Event;

class FlushCache
{
    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(Event $event)
    {
        Cache::clear();
        Stache::clear();
    }
}
