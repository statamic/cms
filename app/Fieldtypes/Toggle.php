<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Toggle extends Fieldtype
{
    public function process($data)
    {
        return (bool) $data;
    }
}
