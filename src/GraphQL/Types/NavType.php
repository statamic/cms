<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Str;

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
            'tree' => [
                'type' => Type::listOf(GraphQL::type(TreeBranchType::NAME)),
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

            if ($field === 'tree') {
                return (new TreeBuilder)->build([
                    'structure' => $nav->handle(),
                    'site' => Site::default()->handle(),
                ]);
            }
        };
    }
}
