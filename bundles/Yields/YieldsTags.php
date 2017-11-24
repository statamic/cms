<?php

namespace Statamic\Addons\Yields;

use Statamic\View\Store;
use Statamic\Extend\Tags;

class YieldsTags extends Tags
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        if ($yield = app(Store::class)->sections()->get($name)) {
            return $yield;
        }

        return $this->isPair ? $this->parse() : null;
    }
}
