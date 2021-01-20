<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;

class AssetInterface extends InterfaceType
{
    const NAME = 'AssetInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        $fields = [
            'path' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'extension' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
        ];

        foreach (GraphQL::getExtraTypeFields(static::NAME) as $field => $closure) {
            $fields[$field] = $closure();
        }

        return $fields;
    }

    public function resolveType(Asset $asset)
    {
        $type = GraphQL::type(
            AssetType::buildName($asset->container())
        );

        return $type;
    }

    public static function addTypes()
    {
        GraphQL::addType(self::class);

        GraphQL::addTypes(AssetContainer::all()->each(function ($container) {
            $container->blueprint()->addGqlTypes();
        })->mapInto(AssetType::class)->all());
    }
}
