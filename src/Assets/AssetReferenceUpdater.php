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
     * @param  string  $container
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
        $this
            ->updateAssetsFieldValues($fields, $dottedPrefix)
            ->updateLinkFieldValues($fields, $dottedPrefix)
            ->updateBardFieldValues($fields, $dottedPrefix)
            ->updateMarkdownFieldValues($fields, $dottedPrefix)
            ->updateNestedFieldValues($fields, $dottedPrefix);
    }

    /**
     * Update assets field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
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
     * Update link field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     * @return $this
     */
    protected function updateLinkFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'link'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->updateStatamicUrlsInLinkValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Update bard field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
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
                    : $this->updateStatamicUrlsInArrayValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Update markdown field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
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
     * @param  \Statamic\Fields\Field  $field
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
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    protected function updateStatamicUrlsInStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $originalValue = $value = Arr::get($data, $dottedKey);

        if (! $originalValue) {
            return;
        }

        $value = preg_replace_callback('/([("])(statamic:\/\/[^()"]*::)([^)"]*)([)"])/im', function ($matches) {
            $newValue = $this->isRemovingValue() ? '' : $matches[2].$this->newValue;

            return $matches[3] === $this->originalValue
                ? $matches[1].$newValue.$matches[4]
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
     * Update asset references in link values.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    private function updateStatamicUrlsInLinkValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $originalValue = $value = Arr::get($data, $dottedKey);

        if (! $originalValue) {
            return;
        }

        if ($value !== "asset::{$this->container}::{$this->originalValue}") {
            return;
        }

        $newValue = $this->isRemovingValue()
            ? null
            : "asset::{$this->container}::{$this->newValue}";

        if ($originalValue === $newValue) {
            return;
        }

        if ($this->isRemovingValue()) {
            Arr::forget($data, $dottedKey);
        } else {
            Arr::set($data, $dottedKey, $newValue);
        }

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Update asset references in bard set on item.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    protected function updateStatamicUrlsInArrayValue($field, $dottedPrefix)
    {
        $this->updateStatamicUrlsInImageNodes($field, $dottedPrefix);
        $this->updateStatamicUrlsInLinkNodes($field, $dottedPrefix);
    }

    /**
     * Update asset references in bard image nodes.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    private function updateStatamicUrlsInImageNodes($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $bardPayload = Arr::get($data, $dottedKey, []);

        if (! $bardPayload) {
            return;
        }

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
                Arr::set($bardPayload, $key, $this->isRemovingValue() ? '' : $value);
            });

        if ($changed->isEmpty()) {
            return;
        }

        Arr::set($data, $dottedKey, $bardPayload);

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Update asset references in bard link nodes.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    private function updateStatamicUrlsInLinkNodes($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $bardPayload = Arr::get($data, $dottedKey, []);

        if (! $bardPayload) {
            return;
        }

        $changed = collect(Arr::dot($bardPayload))
            ->filter(function ($value, $key) {
                return preg_match('/(.*)\.(type)/', $key) && $value === 'link';
            })
            ->mapWithKeys(function ($value, $key) use ($bardPayload) {
                $key = str_replace('.type', '.attrs.href', $key);

                return [$key => Arr::get($bardPayload, $key)];
            })
            ->filter(function ($value) {
                return $value === "statamic://asset::{$this->container}::{$this->originalValue}";
            })
            ->map(function ($value) {
                return "statamic://asset::{$this->container}::{$this->newValue}";
            })
            ->each(function ($value, $key) use (&$bardPayload) {
                Arr::set($bardPayload, $key, $this->isRemovingValue() ? '' : $value);
            });

        if ($changed->isEmpty()) {
            return;
        }

        Arr::set($data, $dottedKey, $bardPayload);

        $this->item->data($data);

        $this->updated = true;
    }
}
