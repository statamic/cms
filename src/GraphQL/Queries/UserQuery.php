<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\UserType;

class UserQuery extends Query
{
    protected $attributes = [
        'name' => 'user',
    ];

    public function type(): Type
    {
        return GraphQL::type(UserType::NAME);
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::string(),
            'email' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $query = User::query();

        if ($id = $args['id'] ?? null) {
            $query->where('id', $id);
        }

        if ($email = $args['email'] ?? null) {
            $query->where('email', $email);
        }

        return $query->limit(1)->get()->first();
    }
}
