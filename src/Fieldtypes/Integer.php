<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Integer extends Fieldtype
{
    public function preProcess($data)
    {
        if ($data === null) {
            return null;
        }

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
