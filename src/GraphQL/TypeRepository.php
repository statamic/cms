<?php

namespace Statamic\GraphQL;

use Statamic\GraphQL\Types\Query;

class TypeRepository
{
    protected $types = [];

    public function get($class)
    {
        $name = $class;
        $isClass = false;

        if (class_exists($class)) {
            $isClass = true;
            $name = $class::name();
        }

        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        if ($isClass) {
            $type = new $class([]);
        }

        return $this->types[$name] = $type ?? null;
    }

    public function query()
    {
        return $this->get(Query::class);
    }
}
