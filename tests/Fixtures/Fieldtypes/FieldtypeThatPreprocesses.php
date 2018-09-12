<?php

namespace Tests\Fixtures\Fieldtypes;

use Statamic\Extend\Fieldtype;

class FieldtypeThatPreprocesses extends Fieldtype
{
    public function preProcess($data)
    {
        return 'preprocessed ' . $data;
    }
}
