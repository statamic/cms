<?php

namespace Statamic\Addons\In;

use Statamic\Extend\Tags;

class InTags extends Tags
{
    /**
     * Maps to {{ in:[group] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
     */
    public function __call($method, $args)
    {
        if ($this->api()->in($method)) {
            return $this->parse([]);
        }
    }
}
