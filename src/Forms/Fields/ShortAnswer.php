<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class ShortAnswer extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $description = 'A simple field for short, one-line answers.';
    protected $icon = 'text-short';
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
            'type' => 'text',
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'placeholder']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => "What's your secret talent?",
            ],
            'value' => 'I can whistle with crackers in my mouth',
        ];
    }
}
