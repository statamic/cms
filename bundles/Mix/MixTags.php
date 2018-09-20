<?php

namespace Statamic\Addons\Mix;

use Statamic\Extend\Tags;

class MixTags extends Tags
{
    public function index()
    {
        return mix(
            $this->get(['src', 'path']),
            $this->get(['from', 'in'], '')
        );
    }
}
