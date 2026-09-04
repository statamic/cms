<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Email extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $description = "Collects an email address and ensures it's properly formatted.";
    protected $icon = 'mail-sign-at';
    protected $categories = ['contact'];
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
