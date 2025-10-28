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
        AssetSaved::class => 'refreshAsset',
        AssetDeleted::class => 'invalidateAsset',
        EntrySaved::class => 'refreshEntry',
        EntryDeleting::class => 'invalidateEntry',
        EntryScheduleReached::class => 'invalidateEntry',
        LocalizedTermSaved::class => 'invalidateTerm',
        LocalizedTermDeleted::class => 'invalidateTerm',
        GlobalVariablesSaved::class => 'refreshGlobalVariables',
        GlobalVariablesDeleted::class => 'invalidateGlobalVariables',
        NavSaved::class => 'refreshNav',
        NavDeleted::class => 'invalidateNav',
        FormSaved::class => 'refreshForm',
        FormDeleted::class => 'invalidateForm',
        CollectionTreeSaved::class => 'invalidateCollectionByTree',
        CollectionTreeDeleted::class => 'invalidateCollectionByTree',
        NavTreeSaved::class => 'refreshNavByTree',
        NavTreeDeleted::class => 'invalidateNavByTree',
        BlueprintSaved::class => 'refreshByBlueprint',
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

    public function refreshAsset($event)
    {
        $this->invalidator->refresh($event->asset);
    }

    public function invalidateEntry($event)
    {
        $this->invalidator->invalidate($event->entry);
    }

    public function refreshEntry($event)
    {
        $this->invalidator->refresh($event->entry);
    }

    public function invalidateTerm($event)
    {
        $this->invalidator->invalidate($event->term);
    }

    public function refreshTerm($event)
    {
        $this->invalidator->refresh($event->term);
    }

    public function invalidateGlobalVariables($event)
    {
        $this->invalidator->invalidate($event->variables);
    }

    public function refreshGlobalVariables($event)
    {
        $this->invalidator->refresh($event->variables);
    }

    public function invalidateNav($event)
    {
        $this->invalidator->invalidate($event->nav);
    }

    public function refreshNav($event)
    {
        $this->invalidator->refresh($event->nav);
    }

    public function invalidateForm($event)
    {
        $this->invalidator->invalidate($event->form);
    }

    public function refreshForm($event)
    {
        $this->invalidator->refresh($event->form);
    }

    public function invalidateCollectionByTree($event)
    {
        $this->invalidator->invalidate($event->tree);
    }

    public function refreshCollectionByTree($event)
    {
        $this->invalidator->refresh($event->tree->collection());
    }

    public function invalidateNavByTree($event)
    {
        $this->invalidator->invalidate($event->tree);
    }

    public function refreshNavByTree($event)
    {
        $this->invalidator->refresh($event->tree->structure());
    }

    public function invalidateByBlueprint($event)
    {
        if ($event->blueprint->namespace() === 'forms') {
            if ($form = Form::find($event->blueprint->handle())) {
                $this->invalidator->invalidate($form);
            }
        }
    }

    public function refreshByBlueprint($event)
    {
        if ($event->blueprint->namespace() === 'forms') {
            if ($form = Form::find($event->blueprint->handle())) {
                $this->invalidator->refresh($form);
            }
        }
    }
}
