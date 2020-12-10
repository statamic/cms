<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Statamic\Fields\Value;

class AssetContainerType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'AssetContainer';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return collect([
            'handle' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'title' => [
                'type' => Type::nonNull(Type::string()),
            ],
        ])->map(function (array $arr) {
            $arr['resolve'] = $this->resolver();

            return $arr;
        })
        ->all();
    }

    private function resolver()
    {
        return function ($collection, $args, $context, $info) {
            $value = $collection->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
