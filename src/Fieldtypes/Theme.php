<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Theme extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
    {
        // If it's a string, it may be the v5 theme, e.g. "dark", so ignore it.
        return is_string($data) ? null : $data;
    }
}
