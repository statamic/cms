<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class ShortAnswer extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $icon = 'text-short';
    protected $categories = ['text'];
    protected $keywords = ['text'];
    protected $order = 0;

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
            'type' => 'text',
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'placeholder']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('statamic::form-fieldtypes.short_answer.example.display'),
            ],
            'value' => __('statamic::form-fieldtypes.short_answer.example.value'),
        ];
    }
}
