<?php

namespace Statamic\Modifiers;

use Statamic\Support\Str;

class Loader
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
            [$class, $method] = explode('@', $class);
        }

        return [app($class), $method ?? 'index'];
    }
}
