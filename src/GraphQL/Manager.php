<?php

namespace Statamic\GraphQL;

use Rebing\GraphQL\Support\Facades\GraphQL;

class Manager
{
    protected $fields = [];

    public function addField($type, $field, $closure)
    {
        $this->fields[$type][$field] = $closure;
    }

    public function getExtraTypeFields($type)
    {
        return $this->fields[$type] ?? [];
    }

    public function addType($type)
    {
        return GraphQL::addType($type);
    }

    public function addTypes($type)
    {
        return GraphQL::addTypes($type);
    }

    public function type($type)
    {
        return GraphQL::type($type);
    }
}
