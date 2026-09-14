<?php

namespace Statamic\CommandPalette;

use Statamic\Events\Concerns\ListensForContentEvents;
use Statamic\Facades\CommandPalette;

class Subscriber
{
    use ListensForContentEvents;

    public function subscribe($events)
    {
        foreach ($this->events as $event) {
            $events->listen($event, self::class.'@invalidate');
        }
    }

    public function invalidate($event)
    {
        CommandPalette::clearCache();
    }
}
