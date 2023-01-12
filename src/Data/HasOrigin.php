<?php

namespace Statamic\Data;

trait HasOrigin
{
    protected $origin;

    public function values()
    {
        $originFallbackValues = method_exists($this, 'getOriginFallbackValues') ? $this->getOriginFallbackValues() : collect();

        $originValues = $this->hasOrigin() ? $this->origin()->values() : collect();

        $computedData = method_exists($this, 'computedData') ? $this->computedData() : [];

        return collect()
            ->merge($originFallbackValues)
            ->merge($originValues)
            ->merge($this->data)
            ->merge($computedData);
    }

    public function value($key)
    {
        $originFallbackValue = method_exists($this, 'getOriginFallbackValue') ? $this->getOriginFallbackValue($key) : null;

        $originValue = $this->hasOrigin() ? $this->origin()->value($key) : $originFallbackValue;

        $value = $this->has($key) ? $this->get($key) : $originValue;

        if (method_exists($this, 'hasComputedCallback') && $this->hasComputedCallback($key)) {
            return $this->getComputed($key) ?? $value;
        }

        return $value;
    }

    public function origin($origin = null)
    {
        return $this->fluentlyGetOrSet('origin')
            ->getter(function ($origin) {
                if (is_string($origin)) {
                    $this->origin = $origin = $this->getOriginByString($origin);
                }

                return $origin;
            })
            ->args(func_get_args());
    }

    abstract public function getOriginByString($origin);

    public function hasOrigin()
    {
        return $this->origin() !== null;
    }

    public function isRoot()
    {
        return ! $this->hasOrigin();
    }

    public function root()
    {
        $entry = $this;

        while ($entry->hasOrigin()) {
            $entry = $entry->origin();
        }

        return $entry;
    }
}
