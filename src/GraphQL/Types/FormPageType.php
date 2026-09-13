<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class FormPageType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'FormPage';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($page) {
                    return $page->handle();
                },
            ],
            'display' => [
                'type' => GraphQL::string(),
                'resolve' => function ($page) {
                    return $page->display();
                },
            ],
            'instructions' => [
                'type' => GraphQL::string(),
                'resolve' => function ($page) {
                    return $page->instructions();
                },
            ],
            'sections' => [
                'type' => GraphQL::listOf(GraphQL::type(SectionType::NAME)),
                'resolve' => function ($page) {
                    return $page->sections()->all();
                },
            ],
        ];
    }
}
