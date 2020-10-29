<?php

namespace Statamic\Structures;

use Statamic\Events\NavTreeSaved;
use Statamic\Facades\Site;

class NavTree extends Tree
{
    public function path()
    {
        $path = base_path('content/structures/navigation/'.$this->handle());

        if (Site::hasMultiple()) {
            $path .= '/'.$this->locale();
        }

        return $path.'.yaml';
    }

    protected function dispatchSavedEvent()
    {
        NavTreeSaved::dispatch($this);
    }
}
