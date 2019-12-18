<?php

namespace Statamic\Extend\Management;

use Statamic\Support\Str;
use Statamic\Modifiers\ModifierNotFoundException;

class ModifierLoader
{
    /**
     * Loads a modifier.
     *
     * @return array  The class, and the method to be called.
     */
    public function load($name)
    {
        if (! ($modifiers = app('statamic.modifiers'))->has($name)) {
            throw new ModifierNotFoundException($name);
        }

        if (Str::contains($class = $modifiers->get($name), 'CoreModifiers@')) {
            list($class, $method) = explode('@', $class);
        }

        return [app($class), $method ?? 'index'];
    }
}
