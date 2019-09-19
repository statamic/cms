<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Lists extends Fieldtype
{
    protected static $handle = 'list';

    public function preProcess($data)
    {
        if (is_null($data)) {
            return [];
        }

        return (array) $data;
    }

    public function process($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        return collect($data)->reject(function ($item) {
            return in_array($item, [null, ''], true);
        })->values()->all();
    }
}
