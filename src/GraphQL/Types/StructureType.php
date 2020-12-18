<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Str;

class StructureType extends \Rebing\GraphQL\Support\Type
{
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
            'expects_root' => [
                'type' => Type::nonNull(Type::boolean()),
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
        return function ($structure, $args, $context, $info) {
            if (in_array($field = $info->fieldName, ['title', 'handle', 'max_depth', 'expects_root'])) {
                $method = Str::camel($field);

                return $structure->$method();
            }

            if ($field === 'tree') {
                return (new TreeBuilder)->build([
                    'structure' => $structure->handle(),
                    'site' => Site::default()->handle(),
                    'include_home' => $structure->expectsRoot(),
                ]);
            }
        };
    }
}
