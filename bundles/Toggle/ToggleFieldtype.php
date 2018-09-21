<?php

namespace Statamic\Addons\Toggle;

use Statamic\Fields\Fieldtype;

class ToggleFieldtype extends Fieldtype
{
    public function process($data)
    {
        return (bool) $data;
    }
}
