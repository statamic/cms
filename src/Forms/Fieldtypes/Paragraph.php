<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class Paragraph extends FormFieldtype
{
    protected static $fieldtype = 'form_paragraph';
    protected $description = 'A paragraph to provide information in your form.';
    protected $icon = 'text-short';
    protected $categories = ['information'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_paragraph',
            'hide_display' => true,
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
