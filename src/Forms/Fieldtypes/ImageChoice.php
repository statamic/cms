<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class ImageChoice extends FormFieldtype
{
    protected static $fieldtype = 'image_choice';
    protected $description = 'An image-based choice selector for visual options.';
    protected $icon = 'image-select';
    protected $categories = ['media'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'image_choice',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
