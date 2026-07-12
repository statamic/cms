<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;

class PingQuery extends Query
{
    protected $attributes = [
        'name' => 'ping',
    ];

    public function type(): Type
    {
        return GraphQL::string();
    }

    public function resolve()
    {
        return 'pong';
    }
}
