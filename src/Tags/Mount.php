<?php

namespace Statamic\Tags;

use Statamic\Facades\Collection;

class Mount extends Tags
{
    /**
     * {{ mount:* }}.
     */
    public function wildcard($tag)
    {
        return $this->mount($tag);
    }

    /**
     * {{ mount handle="" }}.
     */
    public function index()
    {
        return $this->mount($this->params->get('handle'));
    }

    private function mount($handle)
    {
        if (! $collection = Collection::find($handle)) {
            return;
        }

        return $collection->url();
    }
}
