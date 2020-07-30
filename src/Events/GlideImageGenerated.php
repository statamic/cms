<?php

namespace Statamic\Events;

class GlideImageGenerated extends Event
{
    public $path;

    public function __construct($path)
    {
        $this->path = $path;
    }
}
