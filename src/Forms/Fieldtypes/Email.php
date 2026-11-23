<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Email extends FormFieldtype
{
    protected static $fieldtype = 'text';
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
            'validate' => $this->validationRules(),
            ...Arr::except($this->config(), ['type', 'input_type', 'placeholder', 'validate']),
        ];
    }

    private function validationRules(): array
    {
        $rules = (array) $this->config('validate', []);

        $hasRule = collect($rules)->contains(fn ($rule) => is_string($rule) && ($rule === 'email' || str_starts_with($rule, 'email:')));

        return $hasRule ? array_values($rules) : [...array_values($rules), 'email'];
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
