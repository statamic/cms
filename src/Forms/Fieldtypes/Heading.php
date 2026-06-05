<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class Heading extends FormFieldtype
{
    protected static $fieldtype = 'form_heading';

    public static function aliases(): array
    {
        return [static::$fieldtype];
    }
    protected $description = 'A heading to organize your form.';
    protected $icon = 'heading';
    protected $categories = ['information'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_heading',
            'hide_display' => true,
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
