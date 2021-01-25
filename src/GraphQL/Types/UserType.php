<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;

class UserType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'User';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return User::blueprint()->fields()->toGraphQL()
            ->merge([
                'id' => [
                    'type' => GraphQL::string(),
                ],
                'email' => [
                    'type' => GraphQL::string(),
                ],
            ])
            ->map(function (array $arr) {
                $arr['resolve'] = $arr['resolve'] ?? $this->resolver();

                return $arr;
            })
            ->all();
    }

    protected function resolver()
    {
        return function (UserContract $user, $args, $context, $info) {
            return $user->resolveGqlValue($info->fieldName);
        };
    }
}
