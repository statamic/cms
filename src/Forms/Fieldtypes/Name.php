<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

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
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'placeholder']),
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
