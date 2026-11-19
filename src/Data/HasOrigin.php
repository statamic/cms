<?php

namespace Statamic\Data;

use Statamic\Facades\Blink;

trait HasOrigin
{
    /**
     * @var string
     */
    protected $origin;
    private $cachedKeys;

    public function keys()
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        $originFallbackKeys = method_exists($this, 'getOriginFallbackValues') ? $this->getOriginFallbackValues()->keys() : collect();

        $originKeys = $this->hasOrigin() ? $this->origin()->keys() : collect();

        $computedKeys = method_exists($this, 'computedKeys') ? $this->computedKeys() : [];

        return $this->cachedKeys = collect()
            ->merge($originFallbackKeys)
            ->merge($originKeys)
            ->merge($this->data->keys())
            ->merge($computedKeys);
    }

    public function values()
    {
        return $this->getValues(false);
    }

    public function getValues($wrapComputed)
    {
        $originFallbackValues = method_exists($this, 'getOriginFallbackValues') ? $this->getOriginFallbackValues() : collect();

        $originValues = $this->hasOrigin() ? $this->origin()->values() : collect();

        $computedData = method_exists($this, 'getComputedData') ? $this->getComputedData($wrapComputed) : [];

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
        if (func_num_args() === 0) {
            if (! $this->origin) {
                return null;
            }

            if ($found = Blink::get($this->getOriginBlinkKey())) {
                return $found;
            }

            return tap($this->getOriginByString($this->origin), function ($found) {
                Blink::put($this->getOriginBlinkKey(), $found);
            });
        }

        Blink::forget($this->getOriginBlinkKey());

        $this->origin = is_object($origin) ? $this->getOriginIdFromObject($origin) : $origin;

        return $this;
    }

    abstract public function getOriginByString($origin);

    protected function getOriginBlinkKey()
    {
        return 'origin-'.class_basename($this).'-'.$this->id();
    }

    protected function getOriginIdFromObject($origin)
    {
        return $origin->id();
    }

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
