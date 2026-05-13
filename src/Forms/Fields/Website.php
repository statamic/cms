<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Website extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $description = "Collects a website address and ensures it's properly formatted.";
    protected $icon = 'website';
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
            'input_type' => 'url',
            'placeholder' => $this->config('placeholder'),
            'validate' => array_values(array_unique([...((array) $this->config('validate', [])), 'url'])),
            ...Arr::except($this->config(), ['type', 'input_type', 'placeholder', 'validate']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Your Website',
            ],
            'value' => 'https://schmidt.family',
        ];
    }
}
