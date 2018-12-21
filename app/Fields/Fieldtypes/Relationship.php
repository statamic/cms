<?php

namespace Statamic\Fields\Fieldtypes;

use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class Relationship extends Fieldtype
{
    protected $categories = ['relationship'];

    protected $configFields = [
        'max_items' => ['type' => 'integer'],
    ];

    public function preProcess($data)
    {
        return Arr::wrap($data);
    }
}
