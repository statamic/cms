<?php

namespace Statamic\Assets;

use Statamic\Data\DataReferenceUpdater;
use Statamic\Facades\AssetContainer;
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
     * @param string $container
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
     * @param \Illuminate\Support\Collection $fields
     * @param null|string $dottedPrefix
     */
    protected function recursivelyUpdateFields($fields, $dottedPrefix = null)
    {
        $this
            ->updateAssetsFieldValues($fields, $dottedPrefix)
            ->updateBardFieldValues($fields, $dottedPrefix)
            ->updateMarkdownFieldValues($fields, $dottedPrefix)
            ->updateNestedFieldValues($fields, $dottedPrefix);
    }

    /**
     * Update assets field values.
     *
     * @param \Illuminate\Support\Collection $fields
     * @param null|string $dottedPrefix
     * @return $this
     */
    protected function updateAssetsFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'assets'
                    && $this->getConfiguredAssetsFieldContainer($field) === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $field->get('max_files') === 1
                    ? $this->updateStringValue($field, $dottedPrefix)
                    : $this->updateArrayValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Update bard field values.
     *
     * @param \Illuminate\Support\Collection $fields
     * @param null|string $dottedPrefix
     * @return $this
     */
    protected function updateBardFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'bard'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $field->get('save_html') === true
                    ? $this->updateStatamicUrlsInStringValue($field, $dottedPrefix)
                    : $this->updateStatamicUrlsInBardImage($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Update markdown field values.
     *
     * @param \Illuminate\Support\Collection $fields
     * @param null|string $dottedPrefix
     * @return $this
     */
    protected function updateMarkdownFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'markdown'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->updateStatamicUrlsInStringValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Get configured assets field container, or implied asset container if only one exists.
     *
     * @param \Statamic\Fields\Field $field
     * @return string
     */
    protected function getConfiguredAssetsFieldContainer($field)
    {
        if ($container = $field->get('container')) {
            return $container;
        }

        $containers = AssetContainer::all();

        return $containers->count() === 1
            ? $containers->first()->handle()
            : null;
    }

    /**
     * Update `statamic://` urls in string value on item.
     *
     * @param \Statamic\Fields\Field $field
     * @param null|string $dottedPrefix
     */
    protected function updateStatamicUrlsInStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $originalValue = $value = Arr::get($data, $dottedKey);

        if (! $originalValue) {
            return;
        }

        $value = preg_replace_callback('/([("]statamic:\/\/[^()"]*::)([^)"]*)([)"])/im', function ($matches) {
            return $matches[2] === $this->originalValue
                ? $matches[1].$this->newValue.$matches[3]
                : $matches[0];
        }, $value);

        if ($originalValue === $value) {
            return;
        }

        Arr::set($data, $dottedKey, $value);

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Update asset references in bard set on item.
     *
     * @param \Statamic\Fields\Field $field
     * @param null|string $dottedPrefix
     */
    protected function updateStatamicUrlsInBardImage($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $bardPayload = Arr::get($data, $dottedKey, []);

        $changed = collect(Arr::dot($bardPayload))
            ->filter(function ($value, $key) {
                return preg_match('/(.*)\.(type)/', $key) && $value === 'image';
            })
            ->mapWithKeys(function ($value, $key) use ($bardPayload) {
                $key = str_replace('.type', '.attrs.src', $key);

                return [$key => Arr::get($bardPayload, $key)];
            })
            ->filter(function ($value) {
                return $value === "asset::{$this->container}::{$this->originalValue}";
            })
            ->map(function ($value) {
                return "asset::{$this->container}::{$this->newValue}";
            })
            ->each(function ($value, $key) use (&$bardPayload) {
                Arr::set($bardPayload, $key, $value);
            });

        if ($changed->isEmpty()) {
            return;
        }

        Arr::set($data, $dottedKey, $bardPayload);

        $this->item->data($data);

        $this->updated = true;
    }
}
