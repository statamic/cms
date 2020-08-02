<?php

namespace Statamic\Tags;

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
        $count = $this->params->get('count', 1);

        return trans_choice($key, $count, $this->parameters->all());
    }
}
