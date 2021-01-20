<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
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
        return [
            'path' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
        ];
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
