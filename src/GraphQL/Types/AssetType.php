<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;
use Statamic\Support\Str;

class AssetType extends \Rebing\GraphQL\Support\Type
{
    private $container;
    private $blueprint;

    public function __construct($container)
    {
        $this->container = $container;
        $this->blueprint = $container->blueprint();
        $this->attributes['name'] = static::buildName($container);
    }

    public static function buildName(AssetContainer $container): string
    {
        return 'Asset_'.Str::studly($container->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(AssetInterface::NAME),
        ];
    }

    public function fields(): array
    {
        return $this->blueprint->fields()->toGql()
            ->merge((new AssetInterface)->fields())
            ->merge(collect(GraphQL::getExtraTypeFields($this->name))->map(function ($closure) {
                return $closure();
            }))
            ->map(function (array $arr) {
                $arr['resolve'] = $arr['resolve'] ?? $this->resolver();

                return $arr;
            })
            ->all();
    }

    private function resolver()
    {
        return function (Asset $asset, $args, $context, $info) {
            $value = $asset->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
