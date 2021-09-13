<?php

namespace Statamic\GraphQL;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class Manager
{
    protected $fields = [];
    protected $queries = [];
    protected $middleware = [];

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

    public function nonNull($type)
    {
        return Type::nonNull($type);
    }

    public function listOf($type)
    {
        return Type::listOf($type);
    }

    public function id()
    {
        return Type::id();
    }

    public function string()
    {
        return Type::string();
    }

    public function int()
    {
        return Type::int();
    }

    public function float()
    {
        return Type::float();
    }

    public function boolean()
    {
        return Type::boolean();
    }

    public function paginate($type)
    {
        return GraphQL::paginate($type);
    }

    public function addQuery($query)
    {
        $this->queries[] = $query;
    }

    public function getExtraQueries()
    {
        return $this->queries;
    }

    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function getExtraMiddleware()
    {
        return $this->middleware;
    }
}
