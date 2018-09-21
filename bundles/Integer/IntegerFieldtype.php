<?php

namespace Statamic\Addons\Integer;

use Statamic\Fields\Fieldtype;

class IntegerFieldtype extends Fieldtype
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
