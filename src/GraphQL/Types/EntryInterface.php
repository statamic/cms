<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Facades\Collection;

class EntryInterface extends InterfaceType
{
    const NAME = 'EntryInterface';

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
            'url' => [
                'type' => Type::string(),
            ],
            'uri' => [
                'type' => Type::string(),
            ],
            'edit_url' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'permalink' => [
                'type' => Type::string(),
            ],
            'published' => [
                'type' => Type::nonNull(Type::boolean()),
            ],
            'private' => [
                'type' => Type::nonNull(Type::boolean()),
            ],
            'collection' => [
                'type' => Type::nonNull(GraphQL::type(CollectionType::NAME)),
            ],
        ];
    }

    public function resolveType($entry)
    {
        $type = GraphQL::type(
            EntryType::buildName($entry->collection(), $entry->blueprint())
        );

        return $type;
    }

    public static function addTypes()
    {
        GraphQL::addType(self::class);

        $combinations = Collection::all()
            ->flatMap(function ($collection) {
                return $collection
                    ->entryBlueprints()
                    ->each->addGqlTypes()
                    ->map(function ($blueprint) use ($collection) {
                        return compact('collection', 'blueprint');
                    });
            });

        GraphQL::addTypes($combinations->flatMap(function ($item) {
            return [
                new EntryType($item['collection'], $item['blueprint']),
                new EntryPageType($item['collection'], $item['blueprint']),
            ];
        })->all());
    }
}
