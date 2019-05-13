<?php

namespace Statamic\Tags;

use Statamic\API\Arr;
use Statamic\Tags\Collection\Collection;

class GetContent extends Collection
{
    /**
     * {{ get_content:* }} ... {{ /get_content:* }}
     */
    public function __call($method, $args)
    {
        $from = Arr::get($this->context, $method)->raw();

        if (is_array($from)) {
            $from = implode('|', $from);
        }

        $this->parameters['from'] = $from;

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
