<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Heading extends FormFieldtype
{
    protected static $fieldtype = 'form_heading';
    protected $icon = 'heading';
    protected $categories = ['information'];
    protected $collectsValue = false;

    protected function configFieldItems(): array
    {
        return [
            'display' => [
                'display' => __('Heading'),
                'type' => 'text',
                'focus' => true,
                'validate' => 'required',
            ],
            'handle' => FormField::commonFieldOptions()->get('handle')->config(),
            'instructions' => ['type' => 'hidden'],
            'subheading' => [
                'display' => __('Subheading'),
                'type' => 'textarea',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_heading',
            'hide_display' => true,
            'display' => $this->config('display'),
            'subheading' => $this->config('subheading'),
            'listable' => false,
            ...Arr::except($this->config(), ['type', 'display', 'subheading', 'listable']),
        ];
    }
}
