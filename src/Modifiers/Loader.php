<?php

namespace Statamic\Modifiers;

use Statamic\Support\Str;
use Statamic\View\State\StateManager;

class Loader
{
    /**
     * Loads a modifier.
     *
     * @return array The class, and the method to be called.
     */
    public function load($name)
    {
        $key = Str::snake($name);

        if (! ($modifiers = app('statamic.modifiers'))->has($key)) {
            throw new ModifierNotFoundException($name);
        }

        if (Str::contains($class = $modifiers->get($key), 'CoreModifiers@')) {
            [$class, $method] = explode('@', $class);
        }

        StateManager::track($class);

        return [app($class), $method ?? 'index'];
    }
}
