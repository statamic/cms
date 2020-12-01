<?php

namespace Statamic\GraphQL;

use Statamic\GraphQL\Types\Query;

class TypeRepository
{
    protected $types = [];

    public function get($class)
    {
        if (isset($this->types[$class])) {
            return $this->types[$class];
        }

        $instance = new $class([]);

        return $this->types[$class] = $instance;
    }

    public function query()
    {
        return $this->get(Query::class);
    }
}
