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
use Statamic\Events\GlobalVariablesDeleted;
use Statamic\Events\GlobalVariablesSaved;
use Statamic\Events\LocalizedTermDeleted;
use Statamic\Events\LocalizedTermSaved;
use Statamic\Events\NavDeleted;
use Statamic\Events\NavSaved;
use Statamic\Events\NavTreeDeleted;
use Statamic\Events\NavTreeSaved;
use Statamic\Facades\Form;

class Invalidate implements ShouldQueue
{
    protected $invalidator;

    protected $events = [
        AssetSaved::class => 'invalidateAsset',
        AssetDeleted::class => 'invalidateAsset',
        EntrySaved::class => 'invalidateEntry',
        EntryDeleting::class => 'invalidateEntry',
        EntryScheduleReached::class => 'invalidateEntry',
        LocalizedTermSaved::class => 'invalidateTerm',
        LocalizedTermDeleted::class => 'invalidateTerm',
        GlobalVariablesSaved::class => 'invalidateGlobalSet',
        GlobalVariablesDeleted::class => 'invalidateGlobalSet',
        NavSaved::class => 'invalidateNav',
        NavDeleted::class => 'invalidateNav',
        FormSaved::class => 'invalidateForm',
        FormDeleted::class => 'invalidateForm',
        CollectionTreeSaved::class => 'invalidateCollectionByTree',
        CollectionTreeDeleted::class => 'invalidateCollectionByTree',
        NavTreeSaved::class => 'invalidateNavByTree',
        NavTreeDeleted::class => 'invalidateNavByTree',
        BlueprintSaved::class => 'invalidateByBlueprint',
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
        $this->invalidator->invalidate($event->variables);
    }

    public function invalidateNav($event)
    {
        $this->invalidator->invalidate($event->nav);
    }

    public function invalidateForm($event)
    {
        $this->invalidator->invalidate($event->form);
    }

    public function invalidateCollectionByTree($event)
    {
        $this->invalidator->invalidate($event->tree);
    }

    public function invalidateNavByTree($event)
    {
        $this->invalidator->invalidate($event->tree);
    }

    public function invalidateByBlueprint($event)
    {
        if ($event->blueprint->namespace() === 'forms') {
            if ($form = Form::find($event->blueprint->handle())) {
                $this->invalidator->invalidate($form);
            }
        }
    }
}
