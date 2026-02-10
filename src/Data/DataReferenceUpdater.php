<?php

namespace Statamic\Data;

use Statamic\Facades\Blink;
use Statamic\Fieldtypes\UpdatesReferences;
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
     * @var bool
     */
    protected $shouldSave = true;

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
     * Get fieldtypes that use the UpdatesReferences trait, filtered by cheap string type check.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @return \Illuminate\Support\Collection
     */
    protected function fieldsWithReferenceUpdates($fields)
    {
        $handles = Blink::once('fieldtypes-with-reference-updates', fn () => app('statamic.fieldtypes')
            ->filter(fn ($class) => in_array(UpdatesReferences::class, class_uses_recursive($class)))
            ->keys()->all()
        );

        return $fields->filter(fn ($field) => in_array($field->type(), $handles));
    }

    /**
     * Process nested fields for reference updates at the given dotted prefix.
     *
     * @param  \Statamic\Fields\Fields  $fields
     * @param  string  $dottedPrefix
     */
    public function processNestedFields($fields, $dottedPrefix): void
    {
        $this->recursivelyUpdateFields($fields->all(), $dottedPrefix);
    }

    /**
     * Update nested field values by delegating to fieldtype iterateReferenceFields.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     * @return $this
     */
    protected function updateNestedFieldValues($fields, $dottedPrefix)
    {
        $this->fieldsWithReferenceUpdates($fields)
            ->each(function ($field) use ($dottedPrefix) {
                $fieldKey = $dottedPrefix.$field->handle();
                $fieldData = Arr::get($this->item->data()->all(), $fieldKey);

                if ($fieldData === null) {
                    return;
                }

                $field->fieldtype()->iterateReferenceFields(
                    $fieldData,
                    new NestedFieldUpdater($this, $fieldKey)
                );
            });

        return $this;
    }

    /**
     * Disable saving after updating references.
     *
     * @return $this
     */
    public function withoutSaving()
    {
        $this->shouldSave = false;

        return $this;
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
     * Save item without triggering individual git commits, as these should be batched into one larger commit.
     */
    protected function saveItem()
    {
        if (! $this->shouldSave) {
            return;
        }

        GitSubscriber::withoutListeners(function () {
            $this->item->save();
        });
    }
}
