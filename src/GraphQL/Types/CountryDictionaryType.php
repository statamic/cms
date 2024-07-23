<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class CountryDictionaryType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'CountryDictionary';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'name' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'iso2' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
        ];
    }
}
