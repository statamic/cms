<?php

namespace Statamic\Fields;

use Exception;

class FieldtypeRepository
{
    public function preloadable()
    {
        return app('statamic.fieldtypes')->filter(function ($class) {
            return $class::preloadable();
        });
    }

    public function find($handle)
    {
        if (! ($fieldtypes = app('statamic.fieldtypes'))->has($handle)) {
            throw new \Statamic\Exceptions\FieldtypeNotFoundException("Fieldtype [$handle] does not exist.");
        }

        return app($fieldtypes->get($handle));
    }
}
