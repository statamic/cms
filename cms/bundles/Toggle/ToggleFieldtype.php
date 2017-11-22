<?php

namespace Statamic\Addons\Toggle;

use Statamic\Extend\Fieldtype;

class ToggleFieldtype extends Fieldtype
{
    public function process($data)
    {
        return (bool) $data;
    }
}
