<?php

namespace Statamic\Addons\Section;

use Statamic\Extend\Fieldtype;

class SectionFieldtype extends Fieldtype
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
