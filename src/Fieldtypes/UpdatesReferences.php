<?php

namespace Statamic\Fieldtypes;

use Statamic\Data\NestedFieldUpdater;
use Statamic\Support\Arr;

/**
 * Trait for fieldtypes to participate in reference updates (assets, terms, etc.).
 *
 * Override only the methods you need:
 * - replaceAssetReferences() for direct asset references
 * - replaceTermReferences() for direct term references
 * - iterateReferenceFields() for nested Statamic fields
 */
trait UpdatesReferences
{
    /**
     * Replace asset references in the fieldtype's data.
     *
     * @param  mixed  $data  Current field data
     * @param  string|null  $newValue  New asset path (null if removing)
     * @param  string  $oldValue  Old asset path
     * @param  string  $container  Asset container handle
     * @return mixed Modified data (or null to remove field value)
     */
    public function replaceAssetReferences($data, ?string $newValue, string $oldValue, string $container)
    {
        return $data;
    }

    /**
     * Replace term references in the fieldtype's data.
     *
     * @param  mixed  $data  Current field data
     * @param  string|null  $newValue  New term slug (null if removing)
     * @param  string  $oldValue  Old term slug
     * @param  string  $taxonomy  Taxonomy handle
     * @return mixed Modified data (or null to remove field value)
     */
    public function replaceTermReferences($data, ?string $newValue, string $oldValue, string $taxonomy)
    {
        return $data;
    }

    /**
     * Iterate nested fields for reference updates.
     * Override this if your fieldtype contains nested Statamic fields.
     *
     * @param  mixed  $data  Current field data
     * @param  NestedFieldUpdater  $updater  Call $updater->update(Fields $fields, string $relativeDottedPrefix)
     */
    public function iterateReferenceFields($data, NestedFieldUpdater $updater): void
    {
        // Default: no nested fields to process
    }

    /**
     * Helper: Replace a single value by exact comparison.
     *
     * @param  mixed  $data
     * @param  mixed  $newValue
     * @param  mixed  $oldValue
     * @return mixed
     */
    protected function replaceValue($data, $newValue, $oldValue)
    {
        return $data === $oldValue ? $newValue : $data;
    }

    /**
     * Replace values in a flat array.
     *
     * e.g. ['one', 'two', 'three'] where newValue is 'four' and oldValue is 'two' will return ['one', 'four', 'three']
     *
     * @param  mixed  $data
     * @param  mixed  $newValue
     * @param  mixed  $oldValue
     * @return mixed
     */
    protected function replaceValuesInArray($data, $newValue, $oldValue)
    {
        if (! is_array($data) || ! $data) {
            return $data;
        }

        $flat = collect(Arr::dot($data));

        if (! $flat->contains($oldValue)) {
            return $data;
        }

        $result = $flat
            ->map(fn ($value) => $value === $oldValue ? $newValue : $value)
            ->filter()
            ->values();

        return $result->isEmpty() ? null : $result->all();
    }

    /**
     * Helper: Replace statamic:// URLs in a string.
     */
    protected function replaceStatamicUrls(string $data, ?string $newValue, string $oldValue): string
    {
        return preg_replace_callback('/([("])(statamic:\/\/[^()"]*::)([^)"]*)([)"])/im', function ($matches) use ($newValue, $oldValue) {
            if ($matches[3] !== $oldValue) {
                return $matches[0];
            }

            $replacement = $newValue === null ? '' : $matches[2].$newValue;

            return $matches[1].$replacement.$matches[4];
        }, $data);
    }
}
