<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class LongAnswer extends FormFieldtype
{
    protected static $fieldtype = 'textarea';
    protected $description = 'A larger field for detailed responses, comments, or messages.';
    protected $icon = 'text-long';
    protected $categories = ['text'];

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
                'display' => 'Tell us about your dream vacation',
            ],
            'value' => 'A cozy cabin in the mountains with a stack of books, endless coffee, and absolutely no wifi.',
        ];
    }
}
