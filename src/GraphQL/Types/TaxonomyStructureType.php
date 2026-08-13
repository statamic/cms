<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Support\Str;

class TaxonomyStructureType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'TaxonomyStructure';

    protected $attributes = [
        'name' => self::NAME,
    ];

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
                'type' => GraphQL::listOf(GraphQL::type(TaxonomyTreeBranchType::NAME)),
                'args' => [
                    'site' => [
                        'type' => GraphQL::string(),
                    ],
                ],
            ],
        ])->map(function (array $arr) {
            $arr['resolve'] = $this->resolver();

            return $arr;
        })->all();
    }

    private function resolver()
    {
        return function ($structure, $args, $context, $info) {
            if (in_array($field = $info->fieldName, ['title', 'handle', 'max_depth', 'expects_root'])) {
                $method = Str::camel($field);

                return $structure->$method();
            }

            if ($field === 'tree') {
                $site = $args['site'] ?? Site::default()->handle();

                return $this->buildTree($structure, $structure->tree()->pages()->all(), $site);
            }
        };
    }

    private function buildTree($structure, $pages, $site, $depth = 1)
    {
        return collect($pages)->map(function ($page) use ($structure, $site, $depth) {
            if (! $term = Term::find($structure->handle().'::'.$page->id())) {
                return null;
            }

            return [
                'term' => $term->in($site),
                'depth' => $depth,
                'children' => $this->buildTree($structure, $page->pages()->all(), $site, $depth + 1),
            ];
        })->filter()->values()->all();
    }
}
