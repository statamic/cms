<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Email extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $description = "Collects an email address and ensures it's properly formatted.";
    protected $icon = 'mail-sign-at';
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
            'input_type' => 'email',
            'placeholder' => $this->config('placeholder'),
            'validate' => array_values(array_unique([...((array) $this->config('validate', [])), 'email'])),
            ...Arr::except($this->config(), ['type', 'input_type', 'placeholder', 'validate']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Email Address',
            ],
            'value' => 'jamie@schmidt.family',
        ];
    }
}
