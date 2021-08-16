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
            'id' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'path' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'extension' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'is_audio' => [
                'type' => GraphQL::boolean(),
            ],
            'is_image' => [
                'type' => GraphQL::boolean(),
            ],
            'is_video' => [
                'type' => GraphQL::boolean(),
            ],
            'blueprint' => [
                'type' => GraphQL::string(),
            ],
            'edit_url' => [
                'type' => GraphQL::string(),
            ],
            'container' => [
                'type' => GraphQL::nonNull(GraphQL::type(AssetContainerType::NAME)),
            ],
            'folder' => [
                'type' => GraphQL::string(),
            ],
            'url' => [
                'type' => GraphQL::string(),
            ],
            'permalink' => [
                'type' => GraphQL::string(),
            ],
            'size' => [
                'type' => GraphQL::string(),
            ],
            'size_bytes' => [
                'type' => GraphQL::int(),
            ],
            'size_kilobytes' => [
                'type' => GraphQL::float(),
            ],
            'size_megabytes' => [
                'type' => GraphQL::float(),
            ],
            'size_gigabytes' => [
                'type' => GraphQL::float(),
            ],
            'size_b' => [
                'type' => GraphQL::int(),
            ],
            'size_kb' => [
                'type' => GraphQL::float(),
            ],
            'size_mb' => [
                'type' => GraphQL::float(),
            ],
            'size_gb' => [
                'type' => GraphQL::float(),
            ],
            'last_modified' => [
                'type' => GraphQL::string(),
            ],
            'focus_css' => [
                'type' => GraphQL::string(),
            ],
            'height' => [
                'type' => GraphQL::float(),
            ],
            'width' => [
                'type' => GraphQL::float(),
            ],
            'orientation' => [
                'type' => GraphQL::string(),
            ],
            'ratio' => [
                'type' => GraphQL::float(),
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
        GraphQL::addTypes(AssetContainer::all()->each(function ($container) {
            $container->blueprint()->addGqlTypes();
        })->mapInto(AssetType::class)->all());
    }
}
