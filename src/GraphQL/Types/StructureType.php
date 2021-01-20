<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Str;

class StructureType extends \Rebing\GraphQL\Support\Type
{
    public function fields(): array
    {
        return collect([
            'handle' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'title' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'max_depth' => [
                'type' => GraphQL::int(),
            ],
            'expects_root' => [
                'type' => GraphQL::nonNull(GraphQL::boolean()),
            ],
            'tree' => [
                'type' => GraphQL::listOf(GraphQL::type(TreeBranchType::NAME)),
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
