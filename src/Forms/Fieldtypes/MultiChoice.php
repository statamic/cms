<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class MultiChoice extends FormFieldtype
{
    protected static $fieldtype = 'radio';
    protected $description = 'A question with a range of answer options. Respondents can only choose one answer.';
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
                'show_hide_toggle' => true,
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

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Why were you late to work?',
                'options' => [
                    'traffic' => 'Traffic was bad',
                    'alarm' => "My alarm didn't go off",
                    'racoon' => 'A racoon stole my keys',
                ],
            ],
            'value' => 'racoon',
        ];
    }
}
