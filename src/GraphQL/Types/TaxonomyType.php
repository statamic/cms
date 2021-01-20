<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;

class TaxonomyType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Taxonomy';

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
        ])->map(function (array $arr) {
            $arr['resolve'] = $this->resolver();

            return $arr;
        })
        ->all();
    }

    private function resolver()
    {
        return function ($taxonomy, $args, $context, $info) {
            $value = $taxonomy->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
