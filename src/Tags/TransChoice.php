<?php

namespace Statamic\Tags;

use function Statamic\trans_choice;

class TransChoice extends Tags
{
    /**
     * The {{ trans_choice }} tag.
     *
     * @return string
     */
    public function wildcard($tag)
    {
        $key = $this->params->get('key', $tag);
        $count = $this->params->int('count', 1);

        return trans_choice($key, $count, $this->params->all());
    }
}
