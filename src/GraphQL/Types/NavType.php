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
            'max_depth' => [
                'type' => Type::int(),
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
            if (in_array($field = $info->fieldName, ['title', 'handle', 'max_depth'])) {
                $method = Str::camel($field);

                return $nav->$method();
            }
        };
    }
}
