<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Spacer extends FormFieldtype
{
    protected static $fieldtype = 'spacer';
    protected $description = 'Add visual spacing between form fields.';
    protected $icon = 'fieldtype-width';
    protected $categories = ['structure'];

    protected function configFieldItems(): array
    {
        return [
            'display' => [
                'display' => __('Label'),
                'instructions' => __('statamic::form-fieldtypes.paragraph.config.display.instructions'),
                'type' => 'text',
                'focus' => true,
                'validate' => 'required',
            ],
            'instructions' => ['type' => 'hidden'],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'spacer',
            'hide_display' => true,
            'listable' => false,
            ...Arr::except($this->config(), ['type', 'listable']),
        ];
    }
}
