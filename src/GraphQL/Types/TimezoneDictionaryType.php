<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class TimezoneDictionaryType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'TimezoneDictionary';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'name' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'offset' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
        ];
    }
}
