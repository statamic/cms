<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class LinkValueType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'LinkValue';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'url' => [
                'type' => GraphQL::string(),
            ],
            'title' => [
                'type' => GraphQL::string(),
            ],
        ];
    }
}
