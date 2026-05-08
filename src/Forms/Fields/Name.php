<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Name extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $icon = 'user-avatar-flush';
    protected $categories = ['contact'];

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
}
