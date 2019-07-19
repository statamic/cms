<?php

namespace Statamic\Addons\Revealer;

use Statamic\Fields\Fieldtype;

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
        // Don't set as null because it can
        // masquerade as lost data.

        // return null;
    }
}
