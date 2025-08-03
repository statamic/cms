<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    use HasSelectOptions {
        process as traitProcess;
    }

    protected $categories = ['controls'];
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
                'display' => __('Appearance'),
                'fields' => [
                    'inline' => [
                        'display' => __('Inline'),
                        'instructions' => __('statamic::fieldtypes.checkboxes.config.inline'),
                        'type' => 'toggle',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    protected function multiple()
    {
        return true;
    }

    public function preProcessValidatable($value)
    {
        return collect($value)->filter()->values()->all();
    }

    public function process($data)
    {
        return collect($this->traitProcess($data))
            ->reject(fn ($value) => $value === null)
            ->values()
            ->all();
    }
}
