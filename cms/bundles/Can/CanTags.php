<?php

namespace Statamic\Addons\Can;

use Statamic\Extend\Tags;

class CanTags extends Tags
{
    /**
     * Maps to {{ can:[permission] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
     */
    public function __call($method, $args)
    {
        if ($this->api()->can($method)) {
            return $this->parse([]);
        }
    }
}
