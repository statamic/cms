<?php

namespace Statamic\Tags;

use Illuminate\Support\Str;

class Site extends Tags
{
    public function wildcard($key)
    {
        $handle = Str::before($key, ':');

        $site = $this->context->get('sites')->firstWhere('handle', $handle);

        if (! $site) {
            return null;
        }

        $data = $site->toAugmentedCollection();

        return Str::contains($key, ':')
            ? $data->get(Str::after($key, ':'))
            : $data;
    }
}
