<?php

namespace Statamic\Structures;

use Facades\Statamic\Structures\CollectionTreeDiff;
use Statamic\Contracts\Structures\CollectionTreeRepository;
use Statamic\Events\CollectionTreeDeleted;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class CollectionTree extends Tree
{
    public function structure()
    {
        return Blink::once('collection-tree-structure-'.$this->handle(), function () {
            return Collection::findByHandle($this->handle())->structure();
        });
    }

    public function path()
    {
        $path = Stache::store('collection-trees')->directory();

        if (Site::hasMultiple()) {
            $path .= $this->locale().'/';
        }

        $handle = $this->collection()->handle();

        return "{$path}{$handle}.yaml";
    }

    protected function dispatchSavedEvent()
    {
        CollectionTreeSaved::dispatch($this);
    }

    protected function dispatchDeletedEvent()
    {
        CollectionTreeDeleted::dispatch($this);
    }

    public function collection()
    {
        return $this->structure()->collection();
    }

    public function diff()
    {
        return CollectionTreeDiff::analyze(
            $this->original['tree'],
            $this->tree,
            $this->structure()->expectsRoot()
        );
    }

    protected function repository()
    {
        return app(CollectionTreeRepository::class);
    }

    public function idKey()
    {
        return 'entry';
    }
}
