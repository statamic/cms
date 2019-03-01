<?php

namespace Statamic\Addons\Table;

use Statamic\API\Helper;
use Statamic\Fields\Fieldtype;

class TableFieldtype extends Fieldtype
{
    public function process($data)
    {
        return collect($data)->map(function ($row) {
            return array_except($row, '_id');
        })->all();
    }
}
