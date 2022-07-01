<?php

namespace Statamic\Data;

trait ContainsComputedData
{
    public function computedData()
    {
        if (! method_exists($this, 'getComputedCallbacks')) {
            return collect();
        }

        return collect($this->getComputedCallbacks())->map(function ($callback, $field) {
            return $callback($this, method_exists($this, 'get') ? $this->get($field) : null);
        });
    }
}
