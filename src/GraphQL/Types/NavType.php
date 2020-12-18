<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;

class NavType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Navigation';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return collect([
            'handle' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'title' => [
                'type' => Type::nonNull(Type::string()),
            ],
        ])->map(function (array $arr) {
            $arr['resolve'] = $this->resolver();

            return $arr;
        })
        ->all();
    }

    private function resolver()
    {
        return function ($nav, $args, $context, $info) {
            switch ($info->fieldName) {
                case 'title':
                    return $nav->title();
                case 'handle':
                    return $nav->handle();
                default:
                    return null;
            }
        };
    }
}
