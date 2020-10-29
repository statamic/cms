<?php

namespace Statamic\Structures;

use Statamic\Events\CollectionStructureTreeSaved;
use Statamic\Facades\Site;

class CollectionStructureTree extends Tree
{
    public function path()
    {
        $path = base_path('content/structures/collections/'.$this->structure()->collection()->handle());

        if (Site::hasMultiple()) {
            $path .= '/'.$this->locale();
        }

        return $path.'.yaml';
    }

    protected function dispatchSavedEvent()
    {
        CollectionStructureTreeSaved::dispatch($this);
    }

    public function collection()
    {
        return $this->structure()->collection();
    }
}
