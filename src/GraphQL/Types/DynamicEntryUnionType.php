<?php

declare(strict_types=1);

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection as IlluminateCollection;
use Rebing\GraphQL\Support\UnionType;
use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\GraphQL;

class DynamicEntryUnionType extends UnionType
{
    protected $attributes = [
        'name' => 'DynamicEntryUnionType',
    ];

    public function __construct(protected array $types)
    {
        $this->attributes['name'] = self::getTypeName($types);
    }

    public static function getTypeName(array $types): string
    {
        $typeNames = array_map(function ($type) {
            return EntryType::buildName($type['collection'], $type['blueprint']);
        }, $types);

        return 'DynamicEntryUnionType_'.implode('_', $typeNames);
    }

    public static function createTypeFor(Collection|array $collections): Type
    {
        $combinations = is_array($collections)
            ? static::combinationsForHandles($collections)
            : static::combinationsFor($collections);

        if (count($combinations) === 0) {
            return GraphQL::type(EntryInterface::NAME);
        }

        if (count($combinations) === 1) {
            return GraphQL::type(EntryType::buildName($combinations[0]['collection'], $combinations[0]['blueprint']));
        }

        $unionType = new static($combinations);
        GraphQL::addType($unionType);

        return GraphQL::type($unionType->name);
    }

    public function types(): array
    {
        return array_map(function ($type) {
            return GraphQL::type(EntryType::buildName($type['collection'], $type['blueprint']));
        }, $this->types);
    }

    public function resolveType($value)
    {
        return GraphQL::type(EntryType::buildName($value->collection(), $value->blueprint()));
    }

    protected static function combinationsFor(Collection $collection): array
    {
        return static::uniqueCombinations(
            static::collectionsFor($collection)
                ->flatMap(fn (Collection $collection) => $collection->entryBlueprints()->map(fn ($blueprint) => [
                    'collection' => $collection,
                    'blueprint' => $blueprint,
                ]))
                ->all()
        );
    }

    protected static function combinationsForHandles(array $handles): array
    {
        return static::uniqueCombinations(
            collect($handles)
                ->flatMap(function ($handle) {
                    $collection = CollectionFacade::find($handle);

                    if (! $collection) {
                        return [];
                    }

                    return static::combinationsFor($collection);
                })
                ->all()
        );
    }

    protected static function uniqueCombinations(array $combinations): array
    {
        return collect($combinations)
            ->unique(fn (array $combination) => EntryType::buildName($combination['collection'], $combination['blueprint']))
            ->values()
            ->all();
    }

    public static function collectionsFor(Collection $collection): IlluminateCollection
    {
        return collect([$collection])->merge(static::mountedCollections($collection));
    }

    protected static function mountedCollections(Collection $collection): IlluminateCollection
    {
        return CollectionFacade::all()->filter(function (Collection $mounted) use ($collection) {
            $mount = $mounted->mount();

            return $mount && $mount->collectionHandle() === $collection->handle();
        });
    }
}
