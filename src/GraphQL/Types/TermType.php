<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Blueprint;
use Statamic\Support\Str;

class TermType extends \Rebing\GraphQL\Support\Type
{
    private $taxonomy;
    private $blueprint;

    public function __construct($taxonomy, $blueprint)
    {
        $this->taxonomy = $taxonomy;
        $this->blueprint = $blueprint;
        $this->attributes['name'] = static::buildName($taxonomy, $blueprint);
    }

    public static function buildName(Taxonomy $taxonomy, Blueprint $blueprint): string
    {
        return 'Term_'.Str::studly($taxonomy->handle()).'_'.Str::studly($blueprint->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(TermInterface::NAME),
        ];
    }

    public function fields(): array
    {
        return $this->blueprint->fields()->toGql()
            ->merge((new TermInterface)->fields())
            ->merge($this->nestingFields())
            ->merge(collect(GraphQL::getExtraTypeFields($this->name))->map(function ($closure) {
                return $closure();
            }))
            ->map(function (array $arr) {
                $arr['resolve'] = $arr['resolve'] ?? $this->resolver();

                return $arr;
            })
            ->all();
    }

    private function nestingFields(): array
    {
        // Only nestable taxonomies get these. On a flat or orderable taxonomy the handles
        // are free for the blueprint to use, so declaring them here would overwrite the
        // user's own fields with the wrong types.
        if (! $this->taxonomy->nestable()) {
            return [];
        }

        return [
            'parent' => [
                'type' => GraphQL::type(TermInterface::NAME),
            ],
            'children' => [
                'type' => GraphQL::listOf(GraphQL::type(TermInterface::NAME)),
            ],
            'ancestors' => [
                'type' => GraphQL::listOf(GraphQL::type(TermInterface::NAME)),
            ],
            'depth' => [
                'type' => GraphQL::int(),
            ],
        ];
    }

    private function resolver()
    {
        return function (Term $term, $args, $context, $info) {
            return $term->resolveGqlValue($info->fieldName);
        };
    }
}
