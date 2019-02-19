<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class TransChoice extends Tags
{
    /**
     * The {{ trans_choice }} tag.
     *
     * @return string
     */
    public function __call($method, $args)
    {
        $key = $this->get('key', $this->tag_method);
        $count = $this->getInt('count', 1);

        return trans_choice($key, $count, $this->parameters);
    }
}
