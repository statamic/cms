<?php

namespace Statamic\Assets;

use Statamic\Fields\Fields;
use Statamic\Support\Arr;

class AssetReferenceUpdater
{
    protected $item;
    protected $container;
    protected $oldPath;
    protected $newPath;
    protected $updated;

    /**
     * Instantiate asset reference updater.
     *
     * @param mixed $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Instantiate asset reference updater.
     *
     * @param mixed $item
     * @return static
     */
    public static function item($item)
    {
        return new static($item);
    }

    /**
     * Update asset references.
     *
     * @param string $container
     * @param string $originalPath
     * @param string $newPath
     */
    public function updateAssetReferences(string $container, string $originalPath, string $newPath)
    {
        $this->container = $container;
        $this->originalPath = $originalPath;
        $this->newPath = $newPath;

        $this->updateAssetsFields();

        if ($this->updated) {
            $this->item->save();
        }
    }

    /**
     * Update assets fields.
     *
     * @param null|string $dottedPrefix
     * @param null|array $fields
     * @return $this
     */
    protected function updateAssetsFields($dottedPrefix = null, $fields = null)
    {
        $fields = $fields
            ? $fields->all()
            : $this->item->blueprint()->fields()->all();

        $fields
            ->filter(function ($field) {
                return $field->type() === 'assets'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $field->get('max_files') === 1
                    ? $this->updateSingleAsset($dottedPrefix, $field)
                    : $this->updateMultipleAssets($dottedPrefix, $field);
            });

        $this->updateNestedAssetsFields($dottedPrefix, $fields);

        return $this;
    }

    /**
     * Update assets field with single file.
     *
     * @param null|string $dottedPrefix
     * @param \Statamic\Fields\Field $field
     */
    protected function updateSingleAsset($dottedPrefix, $field)
    {
        $data = $this->item->data();

        $dottedKey = $dottedPrefix.$field->handle();

        if (Arr::get($data, $dottedKey) !== $this->originalPath) {
            return;
        }

        $this->updateStringReference($dottedKey);
    }

    /**
     * Update assets fields with multiple files.
     *
     * @param null|string $dottedPrefix
     * @param \Statamic\Fields\Field $field
     */
    protected function updateMultipleAssets($dottedPrefix, $field)
    {
        $data = $this->item->data();

        $dottedKey = $dottedPrefix.$field->handle();

        if (! collect(Arr::get($data, $dottedKey))->contains($this->originalPath)) {
            return;
        }

        $this->updateArrayReference($dottedPrefix.$field->handle());
    }

    /**
     * Update asset path reference in string.
     *
     * @param string $dottedKey
     */
    protected function updateStringReference($dottedKey)
    {
        $data = $this->item->data()->all();

        Arr::set($data, $dottedKey, $this->newPath);

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Update asset path reference in array.
     *
     * @param string $dottedKey
     */
    protected function updateArrayReference($dottedKey)
    {
        $data = $this->item->data()->all();

        $fieldData = Arr::dot(Arr::get($data, $dottedKey));

        foreach ($fieldData as $dottedFieldDataKey => $assetPath) {
            if ($assetPath === $this->originalPath) {
                $fieldData[$dottedFieldDataKey] = $this->newPath;
            }
        }

        Arr::set($data, $dottedKey, $fieldData);

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Update nested assets fields.
     *
     * @param null|string $dottedPrefix
     * @param \Illuminate\Support\Collection $fields
     */
    protected function updateNestedAssetsFields($dottedPrefix, $fields)
    {
        $fields
            ->filter(function ($field) {
                return in_array($field->type(), ['replicator', 'grid', 'bard']);
            })
            ->each(function ($field) use ($dottedPrefix) {
                $method = 'update'.ucfirst($field->type()).'AssetsFields';
                $dottedKey = $dottedPrefix.$field->handle();

                $this->{$method}($dottedKey, $field);
            });
    }

    /**
     * Update replicator assets fields.
     *
     * @param string $dottedKey
     * @param \Statamic\Fields\Field $field
     */
    protected function updateReplicatorAssetsFields($dottedKey, $field)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.";
            $setHandle = Arr::get($set, 'type');
            $fields = Arr::get($field->config(), "sets.{$setHandle}.fields");

            if ($setHandle && $fields) {
                $this->updateAssetsFields($dottedPrefix, new Fields($fields));
            }
        });
    }

    /**
     * Update grid assets fields.
     *
     * @param string $dottedKey
     * @param \Statamic\Fields\Field $field
     */
    protected function updateGridAssetsFields($dottedKey, $field)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.";
            $fields = Arr::get($field->config(), "fields");

            if ($fields) {
                $this->updateAssetsFields($dottedPrefix, new Fields($fields));
            }
        });
    }

    /**
     * Update bard assets fields.
     *
     * @param string $dottedKey
     * @param \Statamic\Fields\Field $field
     */
    protected function updateBardAssetsFields($dottedKey, $field)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.attrs.values.";
            $setHandle = Arr::get($set, 'attrs.values.type');
            $fields = Arr::get($field->config(), "sets.{$setHandle}.fields");

            if ($setHandle && $fields) {
                $this->updateAssetsFields($dottedPrefix, new Fields($fields));
            }
        });
    }
}
