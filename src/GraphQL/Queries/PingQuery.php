<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class PingQuery extends Query
{
    public function type(): Type
    {
        return Type::string();
    }

    public function resolve()
    {
        return 'pong';
    }
}
