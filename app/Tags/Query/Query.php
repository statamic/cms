<?php

namespace Statamic\Tags\Query;

use Statamic\Tags\Tags;
use Statamic\Tags\OutputsItems;

class Query extends Tags
{
    use GetsResults, OutputsItems;

    /**
     * {{ query builder="" }} ... {{ /query }}
     */
    public function index()
    {
        return $this->output($this->results($this->parameters->get('builder')));
    }

    /**
     * {{ query:* }} ... {{ /query:* }}
     */
    public function wildcard($tag)
    {
        return $this->output($this->results($this->context->get($tag)));
    }
}