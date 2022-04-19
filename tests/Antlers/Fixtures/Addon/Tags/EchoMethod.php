<?php

namespace Tests\Antlers\Fixtures\Addon\Tags;

use Statamic\Tags\Tags;

class EchoMethod extends Tags
{
    public function __call($method, $args)
    {
        return $this->method;
    }

    public function parameter()
    {
        return $this->params->get('param');
    }
}
