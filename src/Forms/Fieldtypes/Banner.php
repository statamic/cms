<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Rules\Handle;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Banner extends FormFieldtype
{
    protected static $fieldtype = 'form_banner';
    protected $description = 'A banner to highlight important information in your form.';
    protected $icon = 'banner';
    protected $categories = ['information'];

    public function configFieldItems(): array
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
            'text' => [
                'display' => __('Text'),
                'type' => 'textarea',
            ],
            'icon' => [
                'display' => __('Icon'),
                'type' => 'icon',
                'default' => 'lightbulb-idea',
                'clearable' => true,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_banner',
            'hide_display' => true,
            'display' => $this->config('display'),
            'text' => $this->config('text'),
            'icon' => $this->config('icon'),
            'listable' => false,
            ...Arr::except($this->config(), ['type', 'display', 'text', 'icon', 'listable']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('Important'),
                'text' => __('Please review the following information before continuing.'),
                'icon' => 'lightbulb-idea',
            ],
        ];
    }
}
