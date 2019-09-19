<?php

namespace Statamic\StaticCaching;

use Statamic\Events\Data as DataEvents;
use Illuminate\Contracts\Queue\ShouldQueue;

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
            $dispatcher->listen($event, self::class . '@handle');
        }
    }

    public function handle($event)
    {
        $this->invalidator->invalidate($event->data);
    }
}
