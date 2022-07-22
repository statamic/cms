<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;

class CollectionType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Collection';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return collect([
            'handle' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'title' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'structure' => [
                'type' => GraphQL::type(CollectionStructureType::NAME),
            ],
        ])
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
        return function ($collection, $args, $context, $info) {
            if ($info->fieldName === 'structure') {
                return $collection->structure();
            }

            $value = $collection->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
