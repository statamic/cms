<?php

declare(strict_types=1);

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\UnionType;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy as TaxonomyFacade;

class DynamicTermUnionType extends UnionType
{
    protected $attributes = [
        'name' => 'DynamicTermUnionType',
    ];

    public function __construct(protected array $types)
    {
        $this->attributes['name'] = self::getTypeName($types);
    }

    public static function getTypeName(array $types): string
    {
        $typeNames = array_map(function ($type) {
            return TermType::buildName($type['taxonomy'], $type['blueprint']);
        }, $types);

        return 'DynamicTermUnion_'.implode('_', $typeNames);
    }

    public static function createTypeFor(Taxonomy|array $taxonomies): Type
    {
        $combinations = is_array($taxonomies)
            ? static::combinationsForHandles($taxonomies)
            : static::combinationsFor($taxonomies);

        if (count($combinations) === 0) {
            return GraphQL::type(TermInterface::NAME);
        }

        if (count($combinations) === 1) {
            return GraphQL::type(TermType::buildName($combinations[0]['taxonomy'], $combinations[0]['blueprint']));
        }

        $unionType = new static($combinations);
        GraphQL::addType($unionType);

        return GraphQL::type($unionType->name);
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

    protected static function combinationsFor(Taxonomy $taxonomy): array
    {
        return static::uniqueCombinations(
            $taxonomy->termBlueprints()
                ->map(fn ($blueprint) => [
                    'taxonomy' => $taxonomy,
                    'blueprint' => $blueprint,
                ])
                ->all()
        );
    }

    protected static function combinationsForHandles(array $handles): array
    {
        return static::uniqueCombinations(
            collect($handles)
                ->flatMap(function ($handle) {
                    $taxonomy = TaxonomyFacade::find($handle);

                    if (! $taxonomy) {
                        return [];
                    }

                    return static::combinationsFor($taxonomy);
                })
                ->all()
        );
    }

    protected static function uniqueCombinations(array $combinations): array
    {
        return collect($combinations)
            ->unique(fn (array $combination) => TermType::buildName($combination['taxonomy'], $combination['blueprint']))
            ->values()
            ->all();
    }
}
