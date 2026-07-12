<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class TableRowType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'TableRow';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'cells' => [
                'type' => GraphQL::listOf(GraphQL::string()),
            ],
        ];
    }
}
