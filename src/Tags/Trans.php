<?php

namespace Statamic\Tags;

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
