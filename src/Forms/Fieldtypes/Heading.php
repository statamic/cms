<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class Heading extends FormFieldtype
{
    protected static $fieldtype = 'form_heading';
    protected $description = 'A heading to organize your form.';
    protected $icon = 'heading';
    protected $categories = ['information'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_heading',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
