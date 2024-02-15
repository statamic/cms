<?php

namespace Statamic\Data;

use Statamic\Support\Arr;

trait HasDirtyState
{
    protected $original = [];

    abstract public function getDirtyArray();

    /**
     * Is the item or property on the item dirty?
     *
     * @param  null|string|array  $properties
     */
    public function isDirty($properties = null): bool
    {
        $currentValues = $this->getDirtyArray();
        $originalValues = $this->getOriginal();

        if (! $properties) {
            return json_encode($currentValues) !== json_encode($originalValues);
        }

        return collect($properties)->contains(function ($property) use ($currentValues, $originalValues) {
            if (! array_key_exists($property, $currentValues)) {
                $property = 'data.'.$property;
            }

            return data_get($currentValues, $property) !== data_get($originalValues, $property);
        });
    }

    /**
     * Is the item or property on the item clean?
     *
     * @param  null|string|array  $properties
     */
    public function isClean($properties = null): bool
    {
        return ! $this->isDirty($properties);
    }

    public function syncOriginal(): static
    {
        $this->original = $this->getDirtyArray();

        return $this;
    }

    public function getOriginal($key = null, $fallback = null)
    {
        return Arr::get($this->original, $key, $fallback);
    }
}
