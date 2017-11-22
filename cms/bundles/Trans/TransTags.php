<?php

namespace Statamic\Addons\Trans;

use Statamic\Extend\Tags;

class TransTags extends Tags
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
