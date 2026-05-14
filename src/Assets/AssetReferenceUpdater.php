<?php

namespace Statamic\Assets;

use Statamic\Data\DataReferenceUpdater;
use Statamic\Fieldtypes\Sets;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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

    /**
     * Update fields in blueprints and fieldsets.
     *
     * @return void
     */
    protected function updateBlueprintFields()
    {
        if (
            ! ($config = Sets::previewImageConfig())
            || $this->container !== $config['container']
            || ! Str::startsWith($this->originalValue, $config['folder'].'/')
        ) {
            return;
        }

        $contents = $this->item->contents();

        $fieldPaths = $this->findFieldsInBlueprintContents($contents, fieldtypes: ['bard', 'replicator']);

        foreach ($fieldPaths as $fieldPath) {
            $fieldContents = Arr::get($contents, $fieldPath);

            if (! isset($fieldContents['sets'])) {
                continue;
            }

            $fieldContents['sets'] = collect($fieldContents['sets'])
                ->map(function ($setGroup) {
                    if (! isset($setGroup['sets'])) {
                        return $setGroup;
                    }

                    $setGroup['sets'] = collect($setGroup['sets'])
                        ->map(function ($set) {
                            if (isset($set['image'])) {
                                $fullPath = Sets::previewImageConfig()['folder'].'/'.$set['image'];

                                if ($fullPath !== $this->originalValue) {
                                    return $set;
                                }

                                if (Str::startsWith($this->newValue, Sets::previewImageConfig()['folder'].'/')) {
                                    $set['image'] = Str::after($this->newValue, Sets::previewImageConfig()['folder'].'/');
                                } else {
                                    unset($set['image']);
                                }

                                $this->updated = true;
                            }

                            return $set;
                        })
                        ->all();

                    return $setGroup;
                })
                ->all();

            Arr::set($contents, $fieldPath, $fieldContents);
        }

        if ($this->updated) {
            $this->item->setContents($contents);
        }
    }
}
