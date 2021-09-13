<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\TypeBoxing;

use Illuminate\Support\Traits\Macroable;

class BoolBoxObject
{
    use Macroable, AntlersBoxedStandardMethods;

    protected $value = false;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
