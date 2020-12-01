<?php

namespace Statamic\Http\Controllers;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Http\Request;

class GraphQLController
{
    public function index(Request $request)
    {
        $result = GraphQL::executeQuery($this->schema(), $request->input('query'));

        return $result->toArray();
    }

    protected function schema()
    {
        return new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'ping' => [
                        'type' => Type::string(),
                        'resolve' => function () {
                            return 'pong';
                        },
                    ],
                ],
            ]),
        ]);
    }
}
