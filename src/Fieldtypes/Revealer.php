<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Revealer extends Fieldtype
{
    protected $localizable = false;
    protected $validatable = false;
    protected $defaultable = false;

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
