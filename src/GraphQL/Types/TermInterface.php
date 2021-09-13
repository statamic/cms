<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;

class TermInterface extends InterfaceType
{
    const NAME = 'TermInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        $fields = [
            'id' => [
                'type' => GraphQL::nonNull(GraphQL::id()),
            ],
            'title' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'slug' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'url' => [
                'type' => GraphQL::string(),
            ],
            'uri' => [
                'type' => GraphQL::string(),
            ],
            'edit_url' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'permalink' => [
                'type' => GraphQL::string(),
            ],
            'taxonomy' => [
                'type' => GraphQL::nonNull(GraphQL::type(TaxonomyType::NAME)),
            ],
        ];

        foreach (GraphQL::getExtraTypeFields(static::NAME) as $field => $closure) {
            $fields[$field] = $closure();
        }

        return $fields;
    }

    public function resolveType(Term $term)
    {
        $type = GraphQL::type(
            TermType::buildName($term->taxonomy(), $term->blueprint())
        );

        return $type;
    }

    public static function addTypes()
    {
        $combinations = Taxonomy::all()
            ->flatMap(function ($taxonomy) {
                return $taxonomy
                    ->termBlueprints()
                    ->each->addGqlTypes()
                    ->map(function ($blueprint) use ($taxonomy) {
                        return compact('taxonomy', 'blueprint');
                    });
            });

        GraphQL::addTypes($combinations->map(function ($item) {
            return new TermType($item['taxonomy'], $item['blueprint']);
        })->all());
    }
}
