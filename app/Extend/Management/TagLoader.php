<?php

namespace Statamic\Extend\Management;

use Statamic\Exceptions\ResourceNotFoundException;

class TagLoader
{
    public function load($name, $properties)
    {
        if (! ($tags = app('statamic.tags'))->has($name)) {
            throw new ResourceNotFoundException("Could not find files to load the `{$name}` tag.");
        }

        return $this->init($tags->get($name), $properties);
    }

    private function init($class, $properties)
    {
        return tap(app($class), function ($tag) use ($properties) {
            $tag->setProperties($properties);
        });
    }
}
