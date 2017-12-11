<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class FieldtypeLoader
{
    public function load($name, $config)
    {
        if (! ($tags = app('statamic.fieldtypes'))->has($name)) {
            throw new ResourceNotFoundException("Could not find files to load the `{$name}` fieldtype.");
        }

        return $this->init($tags->get($name), $config);
    }

    private function init($class, $config)
    {
        return tap(app($class), function ($fieldtype) use ($config) {
            $fieldtype->setFieldConfig($config);
        });
    }
}
