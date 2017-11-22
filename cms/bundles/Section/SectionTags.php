<?php

namespace Statamic\Addons\Section;

use Statamic\View\Store;
use Statamic\Extend\Tags;

class SectionTags extends Tags
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        app(Store::class)->sections()->put($name, $this->parse());
    }
}
