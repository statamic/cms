<?php

namespace Statamic\GraphQL;

use GraphQL\Type\Definition\Type;
use Statamic\GraphQL\Types\Query;

class TypeRepository
{
    protected $types = [];

    public function get($class, array $args = [])
    {
        $name = $class;
        $isClass = false;

        if (class_exists($class)) {
            $isClass = true;
            $name = $class::name($args);
        }

        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        if ($isClass) {
            $type = new $class($args);
        }

        return $this->types[$name] = $type ?? null;
    }

    public function register(string $name, Type $type)
    {
        $this->types[$name] = $type;
    }

    public function registered()
    {
        return $this->types;
    }

    public function query()
    {
        return $this->get(Query::class);
    }
}
