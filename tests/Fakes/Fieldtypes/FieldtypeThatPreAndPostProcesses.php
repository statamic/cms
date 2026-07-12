<?php

namespace Tests\Fakes\Fieldtypes;

use Statamic\Extend\Fieldtype;

class FieldtypeThatPreAndPostProcesses extends Fieldtype
{
    public function preProcess($data)
    {
        return 'preprocessed '.$data;
    }

    public function process($data)
    {
        return 'processed '.$data;
    }
}
