<?php

namespace Statamic\Tags;

class Link extends Path
{
    public function wildcard($method)
    {
        return $this->getUrlFromId($method);
    }
}
