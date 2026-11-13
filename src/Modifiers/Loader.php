<?php

namespace Statamic\Modifiers;

use ReflectionMethod;
use Statamic\Support\Str;
use Statamic\View\State\StateManager;

class Loader
{
    protected static $acceptsNull = [];

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

        [$class, $method] = $this->resolve($modifiers->get($key));

        StateManager::track($class);

        return [app($class), $method];
    }

    public function acceptsNull($name)
    {
        if (! $binding = app('statamic.modifiers')->get(Str::snake($name))) {
            return false;
        }

        [$class, $method] = $this->resolve($binding);

        return static::$acceptsNull[$binding] ??= method_exists($class, $method)
            && (new ReflectionMethod($class, $method))->getAttributes(AcceptsNull::class);
    }

    protected function resolve($binding)
    {
        if (Str::contains($binding, 'CoreModifiers@')) {
            return explode('@', $binding);
        }

        return [$binding, 'index'];
    }
}
