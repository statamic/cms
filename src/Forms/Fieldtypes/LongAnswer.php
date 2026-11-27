<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class LongAnswer extends FormFieldtype
{
    protected static $fieldtype = 'textarea';
    protected $icon = 'text-long';
    protected $categories = ['text'];
    protected $keywords = ['textarea'];
    protected $order = 2;

    public function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'textarea',
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'placeholder']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('statamic::form-fieldtypes.long_answer.example.display'),
            ],
            'value' => __('statamic::form-fieldtypes.long_answer.example.value'),
        ];
    }
}
