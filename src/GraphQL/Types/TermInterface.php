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
        GraphQL::addType(self::class);

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
