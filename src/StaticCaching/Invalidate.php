<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\EntrySaved;
use Statamic\Events\TermSaved;

class Invalidate implements ShouldQueue
{
    protected $invalidator;

    protected $events = [
        EntrySaved::class => 'invalidateEntry',
        TermSaved::class => 'invalidateTerm',
    ];

    public function __construct(Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
    }

    public function subscribe($dispatcher)
    {
        foreach ($this->events as $event => $method) {
            $dispatcher->listen($event, self::class.'@'.$method);
        }
    }

    public function invalidateEntry(EntrySaved $event)
    {
        $this->invalidator->invalidate($event->entry);
    }

    public function invalidateTerm(TermSaved $event)
    {
        $this->invalidator->invalidate($event->term);
    }
}
