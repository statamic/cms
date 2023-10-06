<?php

namespace Statamic\Fieldtypes;

class RowId
{
    public function generate()
    {
        return str_random(8);
    }

    public function handle(): string
    {
        return config('statamic.system.row_id_handle', 'id');
    }
}
