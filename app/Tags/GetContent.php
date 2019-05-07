<?php

namespace Statamic\Tags;

use Statamic\Tags\Collection\Collection;

class GetContent extends Collection
{
    /**
     * {{ get_content:* }} ... {{ /get_content:* }}
     */
    public function __call($method, $args)
    {
        $this->parameters['from'] = $this->method;

        return $this->index();
    }

    /**
     * {{ get_content from="" }} ... {{ /get_content }}
     */
    public function index()
    {
        $this->parameters['id:matches'] = $this->get(['from', 'id']);
        $this->parameters['from'] = '*';

        return parent::index();
    }
}
