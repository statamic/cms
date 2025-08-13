<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Select extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $keywords = ['select', 'option', 'choice', 'dropdown', 'list'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.select.config.options'),
                        'type' => 'array',
                        'expand' => true,
                        'key_header' => __('Key'),
                        'value_header' => __('Label').' ('.__('Optional').')',
                        'add_button' => __('Add Option'),
                        'width' => '50',
                    ],
                    'taggable' => [
                        'display' => __('Allow additions'),
                        'instructions' => __('statamic::fieldtypes.select.config.taggable'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                        'type' => 'text',
                        'default' => '',
                        'width' => '33',
                    ],
                    'clearable' => [
                        'display' => __('Clearable'),
                        'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '33',
                    ],
                    'searchable' => [
                        'display' => __('Searchable'),
                        'instructions' => __('statamic::fieldtypes.select.config.searchable'),
                        'type' => 'toggle',
                        'default' => true,
                        'width' => '33',
                    ],
                ],
            ],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'multiple' => [
                        'display' => __('Multiple'),
                        'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                        'width' => '50',
                        'if' => ['multiple' => true],
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'cast_booleans' => [
                        'display' => __('Cast Booleans'),
                        'instructions' => __('statamic::fieldtypes.any.config.cast_booleans'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'width' => '50',
                    ],
                ],
            ],
        ];
    }
}
