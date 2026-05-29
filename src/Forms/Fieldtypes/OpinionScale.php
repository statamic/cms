<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class OpinionScale extends FormFieldtype
{
    protected static $fieldtype = 'opinion_scale';
    protected $description = 'An opinion scale for measuring agreement or satisfaction.';
    protected $icon = 'scale-up';
    protected $categories = ['rate'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'opinion_scale',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
