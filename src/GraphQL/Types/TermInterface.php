<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Taxonomy;

class TermInterface extends InterfaceType
{
    const NAME = 'TermInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::ID()),
            ],
            'title' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'slug' => [
                'type' => Type::nonNull(Type::string()),
            ],
        ];
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
                    ->map(function ($blueprint) use ($taxonomy) {
                        $blueprint->fields()->all()->map->fieldtype()->each(function ($fieldtype) {
                            $fieldtype->addGqlTypes();
                        });

                        return compact('taxonomy', 'blueprint');
                    });
            });

        GraphQL::addTypes($combinations->map(function ($item) {
            return new TermType($item['taxonomy'], $item['blueprint']);
        })->all());
    }
}
