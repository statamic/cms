<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Width extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.width.config.options'),
                        'type' => 'list',
                        'default' => [25, 33, 50, 66, 75, 100],
                        'cast_integers' => true,
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'default' => 100,
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }
}
