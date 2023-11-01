<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ButtonGroup extends Fieldtype
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
                        'instructions' => __('statamic::fieldtypes.radio.config.options'),
                        'type' => 'array',
                        'value_header' => __('Label').' ('.__('Optional').')',
                        'add_button' => __('Add Option'),
                        'validate' => [function ($attribute, $value, $fail) {
                            $optionsWithoutKeys = collect($value)->keys()->filter(fn ($key) => empty($key) || $key === 'null');

                            if ($optionsWithoutKeys->isNotEmpty()) {
                                $fail(__('statamic::validation.options_require_keys'));
                            }
                        }],
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }
}
