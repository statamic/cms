<?php

namespace Statamic\Addons\Is;

use Statamic\Extend\Tags;

class IsTags extends Tags
{
    /**
     * Maps to {{ is:[role] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
     */
    public function __call($method, $args)
    {
        if ($this->api()->is($method)) {
            return $this->parse([]);
        }
    }
}
