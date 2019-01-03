<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class FilterLoader
{
    public function load($name, $properties)
    {
        if (! ($filters = app('statamic.filters'))->has($name)) {
            throw new ResourceNotFoundException("Could not find files to load the `{$name}` filter.");
        }

        return $this->init($filters->get($name), $properties);
    }

    private function init($class, $properties)
    {
        return tap(app($class), function ($filter) use ($properties) {
            $filter->setProperties($properties);
        });
    }
}
