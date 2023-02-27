<?php

namespace Statamic\Fieldtypes;

class RowId
{
    public function generate()
    {
        return str_random(8);
    }
}
