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

    public function keys(array $visited = [])
    {
        if (empty($visited) && $this->cachedKeys) {
            return $this->cachedKeys;
        }

        $id = $this->id();

        if (in_array($id, $visited, true)) {
            return collect();
        }

        $visited[] = $id;

        $originFallbackKeys = method_exists($this, 'getOriginFallbackValues') ? $this->getOriginFallbackValues()->keys() : collect();

        $originKeys = $this->hasOrigin() ? $this->origin()->keys($visited) : collect();

        $computedKeys = method_exists($this, 'computedKeys') ? $this->computedKeys() : [];

        $keys = collect()
            ->merge($originFallbackKeys)
            ->merge($originKeys)
            ->merge($this->data->keys())
            ->merge($computedKeys);

        if (count($visited) === 1) {
            $this->cachedKeys = $keys;
        }

        return $keys;
    }

    public function values()
    {
        return $this->getValues(false);
    }

    public function getValues($wrapComputed, array $visited = [])
    {
        $id = $this->id();

        if (in_array($id, $visited, true)) {
            return collect();
        }

        $visited[] = $id;

        $originFallbackValues = method_exists($this, 'getOriginFallbackValues') ? $this->getOriginFallbackValues() : collect();

        $originValues = $this->hasOrigin() ? $this->origin()->getValues($wrapComputed, $visited) : collect();

        $computedData = method_exists($this, 'getComputedData') ? $this->getComputedData($wrapComputed) : [];

        return collect()
            ->merge($originFallbackValues)
            ->merge($originValues)
            ->merge($this->data)
            ->merge($computedData);
    }

    public function value($key, array $visited = [])
    {
        $id = $this->id();

        if (in_array($id, $visited, true)) {
            return null;
        }

        $visited[] = $id;

        $originFallbackValue = method_exists($this, 'getOriginFallbackValue') ? $this->getOriginFallbackValue($key) : null;

        $originValue = $this->hasOrigin() ? $this->origin()->value($key, $visited) : $originFallbackValue;

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

    public function hasOriginCycle(): bool
    {
        $seen = [];
        $entry = $this;

        while ($entry->hasOrigin()) {
            $id = $entry->id();

            if ($id !== null && isset($seen[$id])) {
                return true;
            }

            if ($id !== null) {
                $seen[$id] = true;
            }

            $entry = $entry->origin();

            if (! $entry) {
                break;
            }
        }

        return false;
    }

    public function root()
    {
        $entry = $this;
        $seen = [];

        while ($entry->hasOrigin()) {
            $id = $entry->id();

            if ($id !== null && isset($seen[$id])) {
                break;
            }

            if ($id !== null) {
                $seen[$id] = true;
            }

            $entry = $entry->origin();
        }

        return $entry;
    }
}
