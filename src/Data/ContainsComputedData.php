<?php

namespace Statamic\Data;

trait ContainsComputedData
{
    protected $withComputedData = true;

    public function computedData()
    {
        if (! method_exists($this, 'getComputedCallbacks') || ! $this->withComputedData) {
            return collect();
        }

        return collect($this->getComputedCallbacks())->map(function ($callback, $field) {
            return $callback($this->instanceWithoutComputed(), method_exists($this, 'get') ? $this->get($field) : null);
        });
    }

    public function computedValue($key, $fallback = null)
    {
        if ($this->withComputedData && $callback = $this->getComputedCallbacks()->get($key)) {
            return $callback($this->instanceWithoutComputed(), $fallback);
        }

        return $fallback;
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
