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

        $currentSite = Site::current();
        $site = $this->params->has('site') ? Site::get($this->params->get('site')) : $currentSite;

        // If the target site is on a different domain, return an absolute URL.
        $isDifferentDomain = parse_url($site->absoluteUrl(), PHP_URL_HOST) !== parse_url($currentSite->absoluteUrl(), PHP_URL_HOST);

        return $isDifferentDomain
            ? $collection->absoluteUrl($site->handle())
            : $collection->url($site->handle());
    }
}
