<?php

namespace Statamic\Tags;

use Illuminate\Support\Str;
use Statamic\Facades\Site;

class GetSite extends Tags
{
    /**
     * {{ get_site:* }} ... {{ /get_site:* }}.
     */
    public function wildcard($tag)
    {
        $handle = Str::before($tag, ':');

        if (! $site = Site::get($handle)) {
            throw new \Exception("Site [$handle] does not exist.");
        }

        $data = $site->toAugmentedCollection();

        return Str::contains($tag, ':')
            ? $data->get(Str::after($tag, ':'))
            : $data;
    }

    /**
     * {{ get_site handle="" }} ... {{ /get_site }}.
     */
    public function index()
    {
        if (! $handle = $this->params->get('handle')) {
            throw new \Exception('A site handle is required.');
        }

        if (! $site = Site::get($handle)) {
            throw new \Exception("Site [$handle] does not exist.");
        }

        return $site->toAugmentedCollection();
    }
}
