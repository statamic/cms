<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Fields\DateField;

class EntryInterface extends InterfaceType
{
    const NAME = 'EntryInterface';

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
            'published' => [
                'type' => GraphQL::nonNull(GraphQL::boolean()),
            ],
            'private' => [
                'type' => GraphQL::nonNull(GraphQL::boolean()),
            ],
            'collection' => [
                'type' => GraphQL::nonNull(GraphQL::type(CollectionType::NAME)),
            ],
            'date' => new DateField,
            'last_modified' => new DateField,
        ];

        foreach ($this->extraFields() as $field => $closure) {
            $fields[$field] = $closure();
        }

        return $fields;
    }

    protected function extraFields()
    {
        return GraphQL::getExtraTypeFields(static::NAME);
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
