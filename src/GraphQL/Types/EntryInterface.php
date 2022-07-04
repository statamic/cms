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
            'status' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'collection' => [
                'type' => GraphQL::nonNull(GraphQL::type(CollectionType::NAME)),
            ],
            'blueprint' => [
                'type' => GraphQL::string(),
            ],
            'date' => new DateField,
            'last_modified' => new DateField,
            'locale' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'site' => [
                'type' => GraphQL::nonNull(GraphQL::type(SiteType::NAME)),
            ],
            'parent' => [
                'type' => GraphQL::type(EntryInterface::NAME),
            ],
        ];

        foreach (GraphQL::getExtraTypeFields(static::NAME) as $field => $closure) {
            $fields[$field] = $closure();
        }

        return $fields;
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
