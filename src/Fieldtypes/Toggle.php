<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Toggle extends Fieldtype
{
    protected $defaultValue = false;

    public function process($data)
    {
        return (bool) $data;
    }
}
