<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Trans extends Tags
{
    /**
     * The {{ trans }} tag.
     *
     * @return string
     */
    public function __call($method, $args)
    {
        $key = $this->get('key', $this->tag_method);

        return trans($key, $this->parameters);
    }
}
