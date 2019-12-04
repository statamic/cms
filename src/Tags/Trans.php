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
    public function wildcard($tag)
    {
        $key = $this->get('key', $tag);

        return trans($key, $this->parameters->all());
    }
}
