<?php

namespace Statamic\Events;

class GlideImageGenerated extends Event
{
    public function __construct(public $path, public $params)
    {
    }
}
