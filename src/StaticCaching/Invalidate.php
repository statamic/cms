<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\NavDeleted;
use Statamic\Events\NavSaved;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;

class Invalidate implements ShouldQueue
{
    protected $invalidator;

    protected $events = [
        EntrySaved::class => 'invalidateEntry',
        EntryDeleted::class => 'invalidateEntry',
        TermSaved::class => 'invalidateTerm',
        TermDeleted::class => 'invalidateTerm',
        GlobalSetSaved::class => 'invalidateGlobalSet',
        GlobalSetDeleted::class => 'invalidateGlobalSet',
        NavSaved::class => 'invalidateNav',
        NavDeleted::class => 'invalidateNav',
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

    public function invalidateEntry($event)
    {
        $this->invalidator->invalidate($event->entry);
    }

    public function invalidateTerm($event)
    {
        $this->invalidator->invalidate($event->term);
    }

    public function invalidateGlobalSet($event)
    {
        $this->invalidator->invalidate($event->globals);
    }

    public function invalidateNav($event)
    {
        $this->invalidator->invalidate($event->nav);
    }
}
