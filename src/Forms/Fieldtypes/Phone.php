<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Phone extends FormFieldtype
{
    protected static $fieldtype = 'text';
    protected $description = 'A field for collecting phone numbers.';
    protected $icon = 'mail-sign-hashtag';
    protected $categories = ['contact'];
    protected $order = 4;

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
            'input_type' => 'tel',
            ...Arr::except($this->config(), ['type', 'input_type']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Phone Number',
                'placeholder' => '(555) 123-4567',
            ],
            'value' => '(555) 867-5309',
        ];
    }
}
