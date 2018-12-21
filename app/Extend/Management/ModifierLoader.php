<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class ModifierLoader
{
    /**
     * Loads a modifier.
     *
     * @return array  The class, and the method to be called.
     */
    public function load($name)
    {
        $name = snake_case($name);

        if (! ($modifiers = app('statamic.modifiers'))->has($name)) {
            throw new ResourceNotFoundException("Could not find files to load the `{$name}` modifier.");
        }

        if (str_contains($class = $modifiers->get($name), 'BaseModifiers@')) {
            list($class, $method) = explode('@', $class);
        }

        return [app($class), $method ?? 'index'];
    }
}
