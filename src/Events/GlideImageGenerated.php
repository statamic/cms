<?php

namespace Statamic\Events;

class GlideImageGenerated extends Event
{
    public $path;
    public $params;

    public function __construct($path, $params)
    {
        $this->path = $path;
        $this->params = $params;
    }
}
