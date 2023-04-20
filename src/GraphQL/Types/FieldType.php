<?php

namespace Statamic\GraphQL\Types;

use Illuminate\Support\Arr;
use Statamic\Facades\GraphQL;

class FieldType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Field';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'handle' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($field) {
                    return $field->handle();
                },
            ],
            'type' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($field) {
                    return $field->type();
                },
            ],
            'display' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($field) {
                    return $field->display();
                },
            ],
            'instructions' => [
                'type' => GraphQL::string(),
                'resolve' => function ($field) {
                    return $field->instructions();
                },
            ],
            'width' => [
                'type' => GraphQL::int(),
                'resolve' => function ($field) {
                    return $field->config()['width'] ?? 100;
                },
            ],
            'config' => [
                'type' => GraphQL::type(ArrayType::NAME),
                'resolve' => function ($field) {
                    // Only show values that the fieldtype exposes.
                    $fields = $field->fieldtype()->configFields()->all()->keys()->all();

                    return Arr::only($field->config(), $fields);
                },
            ],
        ];
    }
}
