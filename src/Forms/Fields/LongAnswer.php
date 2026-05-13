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
            'character_limit' => [
                'display' => __('Character Limit'),
                'instructions' => __('statamic::fieldtypes.text.config.character_limit_instructions'),
                'type' => 'integer',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'textarea',
            'placeholder' => $this->config('placeholder'),
            'character_limit' => $this->config('character_limit'),
            ...Arr::except($this->config(), ['type', 'placeholder', 'character_limit']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Tell us about your dream vacation',
            ],
            'value' => "A cozy cabin in the mountains with a stack of books, endless coffee, and absolutely no wifi.",
        ];
    }
}
