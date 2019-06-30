<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Section extends Fieldtype
{
    public function canBeLocalized()
    {
        return false;
    }

    public function canBeValidated()
    {
        return false;
    }

    public function canHaveDefault()
    {
        return false;
    }
}
