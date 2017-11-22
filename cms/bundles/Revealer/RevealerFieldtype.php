<?php

namespace Statamic\Addons\Revealer;

use Statamic\Extend\Fieldtype;

class RevealerFieldtype extends Fieldtype
{
    public function canBeValidated()
    {
        return false;
    }

    public function canHaveDefault()
    {
        return false;
    }

    public function preProcess($data)
    {
        return $data ?: false;
    }

    public function process($data)
    {
        return null;
    }
}
