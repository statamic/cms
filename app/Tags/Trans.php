<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class Trans extends Tag
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
