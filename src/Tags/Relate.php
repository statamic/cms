<?php

namespace Statamic\Tags;

class Relate extends Tags
{
    public function wildcard($tag)
    {
        return $this->context->value($tag);
    }
}
