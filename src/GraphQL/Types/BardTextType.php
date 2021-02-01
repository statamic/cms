<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class BardTextType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'BardText';

    protected $attributes = [
        'name' => self::NAME,
    ];

    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function fields(): array
    {
        return [
            'type' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'text' => [
                'type' => GraphQL::string(),
            ],
        ];
    }
}
