<?php

declare(strict_types=1);

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\UnionType;
use Statamic\Contracts\Entries\Collection;
use Statamic\Fields\Blueprint;

class DynamicEntryUnionType extends UnionType
{
    protected $attributes = [
        'name' => 'DynamicEntryUnionType',
    ];

    public function __construct(protected array $types)
    {
        $this->attributes['name'] = self::getTypeName($types);
    }

    /**
     * Get the name of the dynamic union type.
     *
     * @param  array{collection: Collection, blueprint: Blueprint}  $types
     */
    public static function getTypeName(array $types): string
    {
        $typeNames = array_map(function ($type) {
            return EntryType::buildName($type['collection'], $type['blueprint']);
        }, $types);

        return 'DynamicEntryUnionType_'.implode('_', $typeNames);
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
}
