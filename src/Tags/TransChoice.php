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
    public function wildcard($tag)
    {
        $key = $this->get('key', $tag);
        $count = $this->getInt('count', 1);

        return trans_choice($key, $count, $this->parameters->all());
    }
}
