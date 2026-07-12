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
     * Filter by taxonomy.
     *
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
        $this->fieldsWithReferenceUpdates($fields)
            ->each(function ($field) use ($dottedPrefix) {
                $data = $this->item->data()->all();
                $dottedKey = $dottedPrefix.$field->handle();
                $oldData = Arr::get($data, $dottedKey);

                if ($oldData === null) {
                    return;
                }

                $newData = $field->fieldtype()->replaceTermReferences(
                    $oldData,
                    $this->newValue,
                    $this->originalValue,
                    $this->taxonomy
                );

                if ($oldData === $newData) {
                    return;
                }

                if ($newData === null && $this->isRemovingValue()) {
                    Arr::forget($data, $dottedKey);
                } else {
                    Arr::set($data, $dottedKey, $newData);
                }

                $this->item->data($data);
                $this->updated = true;
            });

        $this->updateNestedFieldValues($fields, $dottedPrefix);
    }
}
