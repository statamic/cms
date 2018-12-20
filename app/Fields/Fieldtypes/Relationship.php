<?php

namespace Statamic\Fields\Fieldtypes;

use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class Relationship extends Fieldtype
{
    public function preProcess($data)
    {
        return Arr::wrap($data);
    }
}
