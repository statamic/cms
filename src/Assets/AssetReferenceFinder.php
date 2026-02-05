<?php

namespace Statamic\Assets;

use Statamic\Facades\AssetContainer;
use Statamic\Fields\Fields;
use Statamic\Support\Arr;

class AssetReferenceFinder
{
    protected $item;
    protected $container;
    protected $assetPath;
    protected $found = false;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public static function item($item)
    {
        return new static($item);
    }

    public function filterByContainer(string $container)
    {
        $this->container = $container;

        return $this;
    }

    public function findReferences(string $assetPath): bool
    {
        $this->assetPath = $assetPath;

        $this->recursivelyFindFields($this->getTopLevelFields());

        return $this->found;
    }

    protected function getTopLevelFields()
    {
        return $this->item->blueprint()->fields()->all();
    }

    protected function recursivelyFindFields($fields, $dottedPrefix = null)
    {
        $this
            ->findAssetsFieldValues($fields, $dottedPrefix)
            ->findLinkFieldValues($fields, $dottedPrefix)
            ->findBardFieldValues($fields, $dottedPrefix)
            ->findMarkdownFieldValues($fields, $dottedPrefix)
            ->findNestedFieldValues($fields, $dottedPrefix);
    }

    protected function findAssetsFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'assets'
                    && $this->getConfiguredAssetsFieldContainer($field) === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->hasStringValue($field, $dottedPrefix)
                    ? $this->findStringValue($field, $dottedPrefix)
                    : $this->findArrayValue($field, $dottedPrefix);
            });

        return $this;
    }

    protected function findLinkFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'link'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->findStatamicUrlsInLinkValue($field, $dottedPrefix);
            });

        return $this;
    }

    protected function findBardFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'bard'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->hasStringValue($field, $dottedPrefix)
                    ? $this->findStatamicUrlsInStringValue($field, $dottedPrefix)
                    : $this->findStatamicUrlsInArrayValue($field, $dottedPrefix);
            });

        return $this;
    }

    protected function findMarkdownFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'markdown'
                    && $field->get('container') === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->findStatamicUrlsInStringValue($field, $dottedPrefix);
            });

        return $this;
    }

    protected function findNestedFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return in_array($field->type(), ['replicator', 'grid', 'group', 'bard']);
            })
            ->each(function ($field) use ($dottedPrefix) {
                $method = 'find'.ucfirst($field->type()).'Children';
                $dottedKey = $dottedPrefix.$field->handle();

                $this->{$method}($field, $dottedKey);
            });

        return $this;
    }

    protected function findReplicatorChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.";
            $setHandle = Arr::get($set, 'type');
            $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

            if ($setHandle && $fields) {
                $this->recursivelyFindFields((new Fields($fields))->all(), $dottedPrefix);
            }
        });
    }

    protected function findGridChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.";
            $fields = Arr::get($field->config(), 'fields');

            if ($fields) {
                $this->recursivelyFindFields((new Fields($fields))->all(), $dottedPrefix);
            }
        });
    }

    protected function findGroupChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $dottedPrefix = "{$dottedKey}.";
        $fields = Arr::get($field->config(), 'fields');

        if ($fields) {
            $this->recursivelyFindFields((new Fields($fields))->all(), $dottedPrefix);
        }
    }

    protected function findBardChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.attrs.values.";
            $setHandle = Arr::get($set, 'attrs.values.type');
            $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

            if ($setHandle && $fields) {
                $this->recursivelyFindFields((new Fields($fields))->all(), $dottedPrefix);
            }
        });
    }

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

    protected function hasStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        return is_string(Arr::get($data, $dottedKey));
    }

    protected function findStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $value = Arr::get($data, $dottedKey);

        if ($value === $this->assetPath) {
            $this->found = true;
        }
    }

    protected function findArrayValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $fieldData = Arr::get($data, $dottedKey, []);

        if (! $fieldData) {
            return;
        }

        $fieldData = collect(Arr::dot($fieldData));

        if ($fieldData->contains($this->assetPath)) {
            $this->found = true;
        }
    }

    protected function findStatamicUrlsInStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $value = Arr::get($data, $dottedKey);

        if (! $value) {
            return;
        }

        if (preg_match('/([("])(statamic:\/\/[^()"]*::)([^)"]*)([)"])/im', $value, $matches)) {
            if ($matches[3] === $this->assetPath) {
                $this->found = true;
            }
        }
    }

    protected function findStatamicUrlsInLinkValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $value = Arr::get($data, $dottedKey);

        if ($value === "asset::{$this->container}::{$this->assetPath}") {
            $this->found = true;
        }
    }

    protected function findStatamicUrlsInArrayValue($field, $dottedPrefix)
    {
        $this->findStatamicUrlsInImageNodes($field, $dottedPrefix);
        $this->findStatamicUrlsInLinkNodes($field, $dottedPrefix);
    }

    protected function findStatamicUrlsInImageNodes($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $bardPayload = Arr::get($data, $dottedKey, []);

        if (! $bardPayload) {
            return;
        }

        $found = collect(Arr::dot($bardPayload))
            ->filter(function ($value, $key) {
                return preg_match('/(.*)\.(type)/', $key) && $value === 'image';
            })
            ->mapWithKeys(function ($value, $key) use ($bardPayload) {
                $key = str_replace('.type', '.attrs.src', $key);

                return [$key => Arr::get($bardPayload, $key)];
            })
            ->contains(function ($value) {
                return $value === "asset::{$this->container}::{$this->assetPath}";
            });

        if ($found) {
            $this->found = true;
        }
    }

    protected function findStatamicUrlsInLinkNodes($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $bardPayload = Arr::get($data, $dottedKey, []);

        if (! $bardPayload) {
            return;
        }

        $found = collect(Arr::dot($bardPayload))
            ->filter(function ($value, $key) {
                return preg_match('/(.*)\.(type)/', $key) && $value === 'link';
            })
            ->mapWithKeys(function ($value, $key) use ($bardPayload) {
                $key = str_replace('.type', '.attrs.href', $key);

                return [$key => Arr::get($bardPayload, $key)];
            })
            ->contains(function ($value) {
                return $value === "statamic://asset::{$this->container}::{$this->assetPath}";
            });

        if ($found) {
            $this->found = true;
        }
    }
}
