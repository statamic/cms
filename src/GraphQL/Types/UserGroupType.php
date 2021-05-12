<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class UserGroupType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'UserGroup';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'handle' => [
                'type' => GraphQL::string(),
                'resolve' => function ($group) {
                    return $group->handle();
                },
            ],
            'title' => [
                'type' => GraphQL::string(),
                'resolve' => function ($group) {
                    return $group->title();
                },
            ],
        ];
    }
}
