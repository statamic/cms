<?php

namespace Statamic\Assets;

use Statamic\Data\DataReferenceUpdater;
use Statamic\Support\Arr;

class AssetReferenceUpdater extends DataReferenceUpdater
{
    /**
     * @var string
     */
    protected $container;

    /**
     * Filter by container.
     *
     * @return $this
     */
    public function filterByContainer(string $container)
    {
        $this->container = $container;

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

                $newData = $field->fieldtype()->replaceAssetReferences(
                    $oldData,
                    $this->newValue,
                    $this->originalValue,
                    $this->container
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
