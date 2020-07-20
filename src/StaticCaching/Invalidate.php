<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events as DataEvents;

class Invalidate implements ShouldQueue
{
    protected $invalidator;

    protected $events = [
        DataEvents\EntrySaved::class,
    ];

    public function __construct(Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
    }

    public function subscribe($dispatcher)
    {
        foreach ($this->events as $event) {
            $dispatcher->listen($event, self::class.'@handle');
        }
    }

    public function handle($event)
    {
        $this->invalidator->invalidate($event->entry);
    }
}
