<?php

namespace Statamic\Data;

use Statamic\Fields\Fields;
use Statamic\Git\Subscriber as GitSubscriber;
use Statamic\Support\Arr;

abstract class DataReferenceUpdater
{
    /**
     * @var mixed
     */
    protected $item;

    /**
     * @var mixed
     */
    protected $originalValue;

    /**
     * @var mixed
     */
    protected $newValue;

    /**
     * @var bool
     */
    protected $updated;

    /**
     * Instantiate data reference updater.
     *
     * @param  mixed  $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Instantiate data reference updater.
     *
     * @param  mixed  $item
     * @return static
     */
    public static function item($item)
    {
        return new static($item);
    }

    /**
     * Update references.
     *
     * @param  mixed  $originalValue
     * @param  mixed  $newValue
     */
    public function updateReferences($originalValue, $newValue)
    {
        $this->originalValue = $originalValue;
        $this->newValue = $newValue;

        $this->recursivelyUpdateFields($this->getTopLevelFields());

        if ($this->updated) {
            $this->saveItem();
        }

        return (bool) $this->updated;
    }

    /**
     * Get top level fields off item blueprint.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTopLevelFields()
    {
        return $this->item->blueprint()->fields()->all();
    }

    /**
     * Recursively update fields (call `updateNestedFieldValues()` to initiate recursion).
     *
     * @param  \Statamic\Fields\Fields  $fields
     * @param  null|string  $dottedPrefix
     */
    abstract protected function recursivelyUpdateFields($fields, $dottedPrefix = null);

    /**
     * Update nested field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     * @return $this
     */
    protected function updateNestedFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return in_array($field->type(), ['replicator', 'grid', 'bard']);
            })
            ->each(function ($field) use ($dottedPrefix) {
                $method = 'update'.ucfirst($field->type()).'Children';
                $dottedKey = $dottedPrefix.$field->handle();

                $this->{$method}($field, $dottedKey);
            });

        return $this;
    }

    /**
     * Update replicator field children.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  string  $dottedKey
     */
    protected function updateReplicatorChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.";
            $setHandle = Arr::get($set, 'type');
            $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

            if ($setHandle && $fields) {
                $this->recursivelyUpdateFields((new Fields($fields))->all(), $dottedPrefix);
            }
        });
    }

    /**
     * Update grid field children.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  string  $dottedKey
     */
    protected function updateGridChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.";
            $fields = Arr::get($field->config(), 'fields');

            if ($fields) {
                $this->recursivelyUpdateFields((new Fields($fields))->all(), $dottedPrefix);
            }
        });
    }

    /**
     * Update bard field children.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  string  $dottedKey
     */
    protected function updateBardChildren($field, $dottedKey)
    {
        $data = $this->item->data();

        $sets = Arr::get($data, $dottedKey);

        collect($sets)->each(function ($set, $setKey) use ($dottedKey, $field) {
            $dottedPrefix = "{$dottedKey}.{$setKey}.attrs.values.";
            $setHandle = Arr::get($set, 'attrs.values.type');
            $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

            if ($setHandle && $fields) {
                $this->recursivelyUpdateFields((new Fields($fields))->all(), $dottedPrefix);
            }
        });
    }

    /**
     * Get original value.
     *
     * @return mixed
     */
    protected function originalValue()
    {
        return $this->originalValue;
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    protected function newValue()
    {
        return $this->newValue;
    }

    /**
     * Check if value is being removed.
     *
     * @return bool
     */
    public function isRemovingValue()
    {
        return is_null($this->newValue);
    }

    /**
     * Determine if field has string value.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     * @return bool
     */
    protected function hasStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        return is_string(Arr::get($data, $dottedKey));
    }

    /**
     * Update string value on item.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    protected function updateStringValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        if (Arr::get($data, $dottedKey) !== $this->originalValue()) {
            return;
        }

        if ($this->isRemovingValue()) {
            Arr::forget($data, $dottedKey);
        } else {
            Arr::set($data, $dottedKey, $this->newValue());
        }

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Update array value on item.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    protected function updateArrayValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $fieldData = Arr::get($data, $dottedKey, []);

        if (! $fieldData) {
            return;
        }

        $fieldData = collect(Arr::dot($fieldData));

        if (! $fieldData->contains($this->originalValue())) {
            return;
        }

        $fieldData = $fieldData
            ->map(function ($value) {
                if ($value === $this->originalValue() && $this->isRemovingValue()) {
                    return null;
                } elseif ($value === $this->originalValue()) {
                    return $this->newValue();
                } else {
                    return $value;
                }
            })
            ->filter()
            ->values();

        if ($fieldData->isEmpty()) {
            Arr::forget($data, $dottedKey);
        } else {
            Arr::set($data, $dottedKey, $fieldData->all());
        }

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Save item without triggering individual git commits, as these should be batched into one larger commit.
     */
    protected function saveItem()
    {
        GitSubscriber::withoutListeners(function () {
            $this->item->save();
        });
    }
}
