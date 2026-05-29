<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class Banner extends FormFieldtype
{
    protected static $fieldtype = 'form_banner';
    protected $description = 'A banner to highlight important information in your form.';
    protected $icon = 'banner';
    protected $categories = ['information'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_banner',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
