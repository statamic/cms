<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Email extends FormFieldtype
{
    protected $icon = 'mail-sign-at';
    protected $categories = ['Contact Info'];
    protected $keywords = ['mailto', 'website'];

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
            'input_type' => 'email',
            'placeholder' => $this->config('placeholder'),
            'validate' => [...((array) $this->config('validate', [])), 'email'],
            ...Arr::except($this->config(), ['type', 'input_type', 'placeholder', 'validate']),
        ];
    }
}
