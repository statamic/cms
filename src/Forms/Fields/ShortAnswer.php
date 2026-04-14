<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class ShortAnswer extends FormFieldtype
{
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
            'type' => 'text',
            'placeholder' => $this->config('placeholder'),
            'character_limit' => $this->config('character_limit'),
            ...Arr::except($this->config(), ['type', 'placeholder', 'character_limit']),
        ];
    }
}
