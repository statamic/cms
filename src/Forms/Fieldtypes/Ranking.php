<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class Ranking extends FormFieldtype
{
    protected static $fieldtype = 'ranking';
    protected $description = 'A ranking input for ordering items by preference.';
    protected $icon = 'rank';
    protected $categories = ['rate'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'ranking',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
