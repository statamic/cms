<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Website extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $icon = 'website';
    protected $categories = ['contact'];
    protected $keywords = ['url'];
    protected $order = 3;

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
            'validate' => $this->validationRules(),
            ...Arr::except($this->config(), ['type', 'input_type', 'placeholder', 'validate']),
        ];
    }

    private function validationRules(): array
    {
        $rules = (array) $this->config('validate', []);

        $hasRule = collect($rules)->contains(fn ($rule) => is_string($rule) && ($rule === 'url' || str_starts_with($rule, 'url:')));

        return $hasRule ? array_values($rules) : [...array_values($rules), 'url'];
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
