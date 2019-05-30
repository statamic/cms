<?php

namespace Statamic\Fieldtypes\Bard;

use Statamic\Fields\Fieldtype;

class Buttons extends Fieldtype
{
    public static $handle = 'bard_buttons_setting';
    protected $selectable = false;
}
