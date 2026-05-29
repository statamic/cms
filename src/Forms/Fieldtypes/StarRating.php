<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class StarRating extends FormFieldtype
{
    protected static $fieldtype = 'star_rating';
    protected $description = 'A star rating input for collecting ratings.';
    protected $icon = 'star';
    protected $categories = ['rate'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'star_rating',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
