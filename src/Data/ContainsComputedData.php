<?php

namespace Statamic\Data;

trait ContainsComputedData
{
    protected $withComputedData = true;

    public function computedData()
    {
        if (! method_exists($this, 'getComputedCallbacks')) {
            return collect();
        }

        return collect($this->getComputedCallbacks())->map(function ($callback, $field) {
            return $this->getComputed($field);
        });
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
