<?php

namespace Statamic\Data;

use Statamic\Support\Arr;

trait HasDirtyState
{
    protected array $original = [];

    abstract public function getCurrentDirtyStateAttributes(): array;

    /**
     * Determine if the item or any of the given attribute(s) have been modified.
     *
     * @param  null|string|array  $attributes
     */
    public function isDirty($attributes = null): bool
    {
        $currentValues = $this->getCurrentDirtyStateAttributes();
        $originalValues = $this->getOriginal();

        if (! $attributes) {
            return json_encode($currentValues) !== json_encode($originalValues);
        }

        return collect($attributes)->contains(function ($property) use ($currentValues, $originalValues) {
            // In an asset, the data key holds all the data. It would be a breaking
            // change to make it the root level, so we'll support both for now.
            if (! array_key_exists($property, $currentValues)) {
                $property = 'data.'.$property;
            }

            return data_get($currentValues, $property) !== data_get($originalValues, $property);
        });
    }

    /**
     * Determine if the item or all the given attribute(s) have remained the same.
     *
     * @param  null|string|array  $attributes
     */
    public function isClean($attributes = null): bool
    {
        return ! $this->isDirty($attributes);
    }

    public function syncOriginal(): static
    {
        $this->original = $this->getCurrentDirtyStateAttributes();

        return $this;
    }

    public function getOriginal($key = null, $fallback = null)
    {
        return Arr::get($this->original, $key, $fallback);
    }
}
