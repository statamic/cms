<?php

namespace Statamic\Tags;

use Statamic\Facades\Collection;
use Statamic\Facades\Site;

class MountUrl extends Tags
{
    /**
     * {{ mount_url:* }}.
     */
    public function wildcard($tag)
    {
        return $this->mount($tag);
    }

    /**
     * {{ mount_url handle="" }}.
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

        $site = $this->params->get('site') ?? Site::current()->handle();

        return $site == Site::current()->handle()
            ? $collection->url($site)
            : $collection->absoluteUrl($site);
    }
}
