<?php

namespace Statamic\Structures;

use Facades\Statamic\Structures\BranchIds;
use Statamic\Contracts\Structures\NavTree as TreeContract;
use Statamic\Contracts\Structures\NavTreeRepository;
use Statamic\Events\NavTreeDeleted;
use Statamic\Events\NavTreeSaved;
use Statamic\Events\NavTreeSaving;
use Statamic\Facades\Blink;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class NavTree extends Tree implements TreeContract
{
    private $structureCache;

    public function structure()
    {
        if ($this->structureCache) {
            return $this->structureCache;
        }

        return $this->structureCache = Blink::once('nav-tree-structure-'.$this->handle(), function () {
            return Nav::findByHandle($this->handle());
        });
    }

    public function path()
    {
        $path = Stache::store('nav-trees')->directory();

        if (Site::multiEnabled()) {
            $path .= $this->locale().'/';
        }

        return "{$path}{$this->handle()}.yaml";
    }

    protected function dispatchSavedEvent()
    {
        NavTreeSaved::dispatch($this);
    }

    protected function dispatchSavingEvent()
    {
        return NavTreeSaving::dispatch($this);
    }

    protected function dispatchDeletedEvent()
    {
        NavTreeDeleted::dispatch($this);
    }

    protected function repository()
    {
        return app(NavTreeRepository::class);
    }

    public function ensureBranchIds()
    {
        $this->tree = BranchIds::ensure($oldTree = $this->tree);

        if ($oldTree !== $this->tree) {
            $this->save();
        }

        return $this;
    }
}
