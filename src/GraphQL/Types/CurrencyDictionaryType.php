<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class CurrencyDictionaryType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'CurrencyDictionary';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'name' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'symbol' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'code' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
        ];
    }
}
