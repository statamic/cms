<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Checkbox Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.checkboxes.config.options'),
                        'type' => 'array',
                        'expand' => true,
                        'field' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'inline' => [
                        'display' => __('Inline'),
                        'instructions' => __('statamic::fieldtypes.checkboxes.config.inline'),
                        'type' => 'toggle',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'taggable',
                    ],
                ],
            ],
        ];
    }

    protected function multiple()
    {
        return true;
    }
}
