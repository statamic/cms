<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Tags extends Fieldtype
{
    public function preProcess($data)
    {
        return ($data) ? $data : [];
    }
}
