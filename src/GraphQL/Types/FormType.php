<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\GraphQL\CastableToValidationString;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;

class FormType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Form';

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
            'honeypot' => [
                'type' => GraphQL::string(),
            ],
            'fields' => [
                'type' => GraphQL::listOf(GraphQL::type(FieldType::NAME)),
                'resolve' => function ($form, $args, $context, $info) {
                    return $form->blueprint()->fields()->all();
                },
            ],
            'rules' => [
                'type' => GraphQL::type(ArrayType::NAME),
                'resolve' => function ($form, $args, $context, $info) {
                    return collect($form->blueprint()->fields()->validator()->rules())
                        ->map(function ($rules) {
                            return collect($rules)->map(function ($rule) {
                                if (is_string($rule)) {
                                    return $rule;
                                }

                                if ($rule instanceof CastableToValidationString) {
                                    return $rule->toGqlValidationString();
                                }

                                return get_class($rule).'::class';
                            });
                        })
                        ->all();
                },
            ],
            'sections' => [
                'type' => GraphQL::listOf(GraphQL::type(SectionType::NAME)),
                'resolve' => function ($form, $args, $context, $info) {
                    return $form->blueprint()->tabs()->first()->sections()->all();
                },
            ],
        ])->map(function (array $arr) {
            $arr['resolve'] = $arr['resolve'] ?? $this->resolver();

            return $arr;
        })->all();
    }

    private function resolver()
    {
        return function (Form $form, $args, $context, $info) {
            $value = $form->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
