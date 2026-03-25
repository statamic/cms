<?php

declare(strict_types=1);

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\UnionType;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Fields\Blueprint;

class DynamicTermUnionType extends UnionType
{
    protected $attributes = [
        'name' => 'DynamicTermUnionType',
    ];

    public function __construct(protected array $types)
    {
        $this->attributes['name'] = self::getTypeName($types);
    }

    /**
     * Get the name of the dynamic union type
     *
     * @param  array{taxonomy: Taxonomy, blueprint: Blueprint}  $types
     */
    public static function getTypeName(array $types): string
    {
        $typeNames = array_map(function ($type) {
            return TermType::buildName($type['taxonomy'], $type['blueprint']);
        }, $types);

        return 'DynamicTermUnion_'.implode('_', $typeNames);
    }

    public function types(): array
    {
        return array_map(function ($type) {
            return GraphQL::type(TermType::buildName($type['taxonomy'], $type['blueprint']));
        }, $this->types);
    }

    public function resolveType($value)
    {
        return GraphQL::type(TermType::buildName($value->term()->taxonomy(), $value->term()->blueprint()));
    }
}
