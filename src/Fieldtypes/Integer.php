<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Integer extends Fieldtype
{
    public function preProcess($data)
    {
        return (int) $data;
    }

    public function process($data)
    {
        if ($data === null || $data === '') {
            return null;
        }

        return (int) $data;
    }
}
