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
        return User::blueprint()->fields()->toGql()
            ->merge([
                'id' => [
                    'type' => GraphQL::string(),
                ],
                'email' => [
                    'type' => GraphQL::string(),
                ],
                'name' => [
                    'type' => GraphQL::string(),
                ],
                'initials' => [
                    'type' => GraphQL::string(),
                ],
                'edit_url' => [
                    'type' => GraphQL::string(),
                ],
            ])
            ->merge(collect(GraphQL::getExtraTypeFields($this->name))->map(function ($closure) {
                return $closure();
            }))
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
