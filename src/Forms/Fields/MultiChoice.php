<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

use function Statamic\trans as __;

class MultiChoice extends FormFieldtype
{
    protected static $fieldtype = 'radio';
    protected $icon = 'fieldtype-radio';
    protected $categories = ['choice'];

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.radio.config.options'),
                'type' => 'array',
                'expand' => true,
                'field' => [
                    'type' => 'text',
                ],
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'radio',
            'options' => $this->config('options'),
            ...Arr::except($this->config(), ['type', 'options']),
        ];
    }
}
