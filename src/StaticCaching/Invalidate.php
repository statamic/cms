<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Events\BlueprintDeleted;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\CollectionTreeDeleted;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntryDeleting;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntryScheduleReached;
use Statamic\Events\FormDeleted;
use Statamic\Events\FormSaved;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\NavDeleted;
use Statamic\Events\NavSaved;
use Statamic\Events\NavTreeDeleted;
use Statamic\Events\NavTreeSaved;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;
use Statamic\Facades\Form;

class Invalidate implements ShouldQueue
{
    protected $invalidator;

    protected $events = [
        AssetSaved::class => 'invalidateAndRecacheAsset',
        AssetDeleted::class => 'invalidateAsset',
        EntrySaved::class => 'invalidateAndRecacheEntry',
        EntryDeleting::class => 'invalidateEntry',
        EntryScheduleReached::class => 'invalidateEntry',
        TermSaved::class => 'invalidateTerm',
        TermDeleted::class => 'invalidateTerm',
        GlobalSetSaved::class => 'invalidateAndRecacheGlobalSet',
        GlobalSetDeleted::class => 'invalidateGlobalSet',
        NavSaved::class => 'invalidateAndRecacheNav',
        NavDeleted::class => 'invalidateNav',
        FormSaved::class => 'invalidateAndRecacheForm',
        FormDeleted::class => 'invalidateForm',
        CollectionTreeSaved::class => 'invalidateAndRecacheCollectionByTree',
        CollectionTreeDeleted::class => 'invalidateCollectionByTree',
        NavTreeSaved::class => 'invalidateAndRecacheNavByTree',
        NavTreeDeleted::class => 'invalidateNavByTree',
        BlueprintSaved::class => 'invalidateAndRecacheByBlueprint',
        BlueprintDeleted::class => 'invalidateByBlueprint',
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

    public function invalidateAsset($event)
    {
        $this->invalidator->invalidate($event->asset);
    }

    public function invalidateAndRecacheAsset($event)
    {
        $this->invalidator->invalidateAndRecache($event->asset);
    }

    public function invalidateEntry($event)
    {
        $this->invalidator->invalidate($event->entry);
    }

    public function invalidateAndRecacheEntry($event)
    {
        $this->invalidator->invalidateAndRecache($event->entry);
    }

    public function invalidateTerm($event)
    {
        $this->invalidator->invalidate($event->term);
    }

    public function invalidateAndRecacheTerm($event)
    {
        $this->invalidator->invalidateAndRecache($event->term);
    }

    public function invalidateGlobalSet($event)
    {
        $this->invalidator->invalidate($event->globals);
    }

    public function invalidateAndRecacheGlobalSet($event)
    {
        $this->invalidator->invalidateAndRecache($event->globals);
    }

    public function invalidateNav($event)
    {
        $this->invalidator->invalidate($event->nav);
    }

    public function invalidateAndRecacheNav($event)
    {
        $this->invalidator->invalidateAndRecache($event->nav);
    }

    public function invalidateForm($event)
    {
        $this->invalidator->invalidate($event->form);
    }

    public function invalidateAndRecacheForm($event)
    {
        $this->invalidator->invalidateAndRecache($event->form);
    }

    public function invalidateCollectionByTree($event)
    {
        $this->invalidator->invalidate($event->tree->collection());
    }

    public function invalidateAndRecacheCollectionByTree($event)
    {
        $this->invalidator->invalidateAndRecache($event->tree->collection());
    }

    public function invalidateNavByTree($event)
    {
        $this->invalidator->invalidate($event->tree->structure());
    }

    public function invalidateAndRecacheNavByTree($event)
    {
        $this->invalidator->invalidateAndRecache($event->tree->structure());
    }

    public function invalidateByBlueprint($event)
    {
        if ($event->blueprint->namespace() === 'forms') {
            if ($form = Form::find($event->blueprint->handle())) {
                $this->invalidator->invalidate($form);
            }
        }
    }

    public function invalidateAndRecacheByBlueprint($event)
    {
        if ($event->blueprint->namespace() === 'forms') {
            if ($form = Form::find($event->blueprint->handle())) {
                $this->invalidator->invalidateAndRecache($form);
            }
        }
    }
}
