<?php

namespace Statamic\Fieldtypes;

use Statamic\Support\Str;

class RowId
{
    public function generate()
    {
        return Str::random(8);
    }

    public function handle(): string
    {
        return config('statamic.system.row_id_handle', 'id');
    }
}
