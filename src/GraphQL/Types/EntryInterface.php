<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Contracts\Entries\Entry;
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
        ];
    }

    public function resolveType(Entry $entry)
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

        GraphQL::addTypes($combinations->map(function ($item) {
            return new EntryType($item['collection'], $item['blueprint']);
        })->all());
    }
}
