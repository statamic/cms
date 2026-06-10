<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
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
            ...Arr::except($this->config(), ['type']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('Important'),
                'instructions' => __('Please review the following information before continuing.'),
                'icon' => 'lightbulb-idea',
            ],
        ];
    }
}
