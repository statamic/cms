<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class SectionType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Section';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'display' => [
                'type' => GraphQL::string(),
                'resolve' => function ($section) {
                    return $section->display();
                },
            ],
            'instructions' => [
                'type' => GraphQL::string(),
                'resolve' => function ($section) {
                    return $section->instructions();
                },
            ],
            'fields' => [
                'type' => GraphQL::listOf(GraphQL::type(FieldType::NAME)),
                'resolve' => function ($section) {
                    return $section->fields()->all();
                },
            ],
        ];
    }
}
