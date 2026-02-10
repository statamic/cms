<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fields;

/**
 * Trait for custom fieldtypes to participate in reference updates (assets, terms, etc.).
 *
 * Override only the methods you need:
 * - replaceAssetReferences() for direct asset references
 * - replaceTermReferences() for direct term references
 * - processNestedFieldsForReferences() for nested Statamic fields
 */
trait UpdatesReferences
{
    /**
     * Replace asset references in the fieldtype's data.
     * Override this if your fieldtype stores direct asset references.
     *
     * @param  mixed  $data  Current field data
     * @param  string|null  $newValue  New asset path (null if removing)
     * @param  string  $oldValue  Old asset path
     * @return mixed Modified data (or null to remove field value)
     */
    public function replaceAssetReferences($data, $newValue, $oldValue)
    {
        return $data;
    }

    /**
     * Replace term references in the fieldtype's data.
     * Override this if your fieldtype stores direct term references.
     *
     * @param  mixed  $data  Current field data
     * @param  string|null  $newValue  New term slug (null if removing)
     * @param  string  $oldValue  Old term slug
     * @return mixed Modified data (or null to remove field value)
     */
    public function replaceTermReferences($data, $newValue, $oldValue)
    {
        return $data;
    }

    /**
     * Process nested fields for reference updates.
     * Override this if your fieldtype contains nested Statamic fields.
     *
     * @param  mixed  $data  Current field data
     * @param  callable  $processFields  fn(Fields $fields, string $relativeDottedPrefix): void
     */
    public function processNestedFieldsForReferences($data, callable $processFields)
    {
        // Default: no nested fields to process
    }

    /**
     * Helper: Process fields for a single (group-like) structure.
     * Resulting prefix: ""
     *
     * @param  array|string  $fieldsConfig
     */
    protected function processSingleNestedFields($fieldsConfig, callable $processFields)
    {
        $fields = $this->resolveFieldsConfigForReferenceUpdates($fieldsConfig);
        $processFields($fields, '');
    }

    /**
     * Helper: Process fields for an array structure at root level.
     * Resulting prefix: "0.", "1.", "2."...
     *
     * @param  mixed  $data
     * @param  array|string  $fieldsConfig
     */
    protected function processArrayNestedFields($data, $fieldsConfig, callable $processFields)
    {
        if (! is_array($data)) {
            return;
        }

        $fields = $this->resolveFieldsConfigForReferenceUpdates($fieldsConfig);

        foreach (array_keys($data) as $idx) {
            $processFields($fields, "{$idx}.");
        }
    }

    /**
     * Helper: Process fields for an array nested under a specific key.
     * Resulting prefix: "{key}.0.", "{key}.1."...
     *
     * @param  mixed  $data
     * @param  string  $key
     * @param  array|string  $fieldsConfig
     */
    protected function processArrayNestedFieldsAtKey($data, $key, $fieldsConfig, callable $processFields)
    {
        $arrayData = is_array($data) ? ($data[$key] ?? []) : [];

        $fields = $this->resolveFieldsConfigForReferenceUpdates($fieldsConfig);

        foreach (array_keys($arrayData) as $idx) {
            $processFields($fields, "{$key}.{$idx}.");
        }
    }

    /**
     * Helper: Process fields for a single structure nested under a key.
     * Resulting prefix: "{key}."
     *
     * @param  string  $key
     * @param  array|string  $fieldsConfig
     */
    protected function processSingleNestedFieldsAtKey($key, $fieldsConfig, callable $processFields)
    {
        $fields = $this->resolveFieldsConfigForReferenceUpdates($fieldsConfig);
        $processFields($fields, "{$key}.");
    }

    /**
     * Resolve fields config to Fields instance.
     *
     * @param  array|string  $fieldsConfig
     * @return \Statamic\Fields\Fields
     */
    private function resolveFieldsConfigForReferenceUpdates($fieldsConfig)
    {
        if (is_string($fieldsConfig)) {
            $config = $this->config($fieldsConfig);
        } else {
            $config = $fieldsConfig;
        }

        return new Fields($config ?? []);
    }
}
