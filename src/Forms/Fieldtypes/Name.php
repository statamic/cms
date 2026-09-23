<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Name extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $description = "Collects someone's name.";
    protected $icon = 'user-avatar-flush';
    protected $categories = ['contact'];
    protected $order = 1;

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
            'autocomplete' => 'name',
            'placeholder' => $this->config('placeholder'),
            'validate' => array_values(array_unique([...((array) $this->config('validate', [])), 'not_regex:/\pN/u'])),
            ...Arr::except($this->config(), ['type', 'autocomplete', 'placeholder', 'validate']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Your Name',
            ],
            'value' => 'Jamie Schmidt',
        ];
    }
}
