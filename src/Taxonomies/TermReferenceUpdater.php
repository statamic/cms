<?php

namespace Statamic\Taxonomies;

use Statamic\Data\DataReferenceUpdater;
use Statamic\Support\Arr;

class TermReferenceUpdater extends DataReferenceUpdater
{
    /**
     * @var string
     */
    protected $taxonomy;

    /**
     * @var null|string
     */
    protected $scope;

    /**
     * Filter by taxonomy.
     *
     * @param  string  $taxonomy
     * @return $this
     */
    public function filterByTaxonomy(string $taxonomy)
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * Recursively update fields.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     */
    protected function recursivelyUpdateFields($fields, $dottedPrefix = null)
    {
        $this
            ->updateTermsFieldValues($fields, $dottedPrefix)
            ->updateScopedTermsFieldValues($fields, $dottedPrefix)
            ->updateNestedFieldValues($fields, $dottedPrefix);
    }

    /**
     * Update terms field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     * @return $this
     */
    protected function updateTermsFieldValues($fields, $dottedPrefix)
    {
        $this->scope = null;

        $fields
            ->filter(function ($field) {
                return $field->type() === 'terms'
                    && in_array($this->taxonomy, Arr::wrap($field->get('taxonomies')));
            })
            ->each(function ($field) use ($dottedPrefix) {
                $field->get('max_items') === 1
                    ? $this->updateStringValue($field, $dottedPrefix)
                    : $this->updateArrayValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Update scoped terms field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     * @return $this
     */
    protected function updateScopedTermsFieldValues($fields, $dottedPrefix)
    {
        $this->scope = "{$this->taxonomy}::";

        $fields
            ->filter(function ($field) {
                return $field->type() === 'terms'
                    && count(Arr::wrap($field->get('taxonomies'))) === 0;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $field->get('max_items') === 1
                    ? $this->updateStringValue($field, $dottedPrefix)
                    : $this->updateArrayValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Get original value.
     *
     * @return mixed
     */
    protected function originalValue()
    {
        return $this->scope.$this->originalValue;
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    protected function newValue()
    {
        return $this->scope.$this->newValue;
    }
}
