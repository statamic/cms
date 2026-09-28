<?php

namespace Statamic\Data;

use Statamic\Fields\Value;

trait ContainsComputedData
{
    protected $withComputedData = true;

    public function computedKeys()
    {
        if (! method_exists($this, 'getComputedCallbacks')) {
            return collect();
        }

        return $this->getComputedCallbacks()->keys();
    }

    public function computedData()
    {
        return $this->getComputedData(false);
    }

    public function getComputedData($wrapInValue)
    {
        if (! method_exists($this, 'getComputedCallbacks')) {
            return collect();
        }

        return collect($this->getComputedCallbacks())
            ->map(fn ($callback, $field) => $wrapInValue ?
                new Value(fn () => $this->getComputed($field)) :
                $this->getComputed($field)
            );
    }

    public function getComputed($key)
    {
        $instance = $this->instanceWithoutComputed();

        $value = method_exists($this, 'get') ? $instance->get($key) : null;

        if ($this->withComputedData && $callback = $this->getComputedCallbacks()->get($key)) {
            return $callback($instance, $value);
        }

        return $value;
    }

    protected function hasComputedCallback($key)
    {
        return $this->getComputedCallbacks()->has($key);
    }

    protected function instanceWithoutComputed()
    {
        $clone = clone $this;

        $clone->withComputedData = false;

        return $clone;
    }
}
