<?php

namespace Statamic\UpdateScripts;

use Carbon\Carbon;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Date as DateFieldtype;
use Statamic\Listeners\Concerns\GetsItemsContainingData;
use Statamic\Support\Arr;

class ConvertDatesToUtc extends UpdateScript
{
    use GetsItemsContainingData;

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        $this
            ->getItemsContainingData()
            ->each(function ($item) {
                /** @var Fields $fields */
                $fields = $item->blueprint()->fields();

                $this->recursivelyUpdateFields($item, $fields);

                if (method_exists($item, 'isDirty')) {
                    $item->isDirty() ? $item->saveQuietly() : null;

                    return;
                }

                $item->saveQuietly();
            });
    }

    private function recursivelyUpdateFields($item, Fields $fields, ?string $dottedPrefix = null): void
    {
        $this
            ->updateDateFields($item, $fields, $dottedPrefix)
            ->updateDateFieldsInGroups($item, $fields, $dottedPrefix)
            ->updateDateFieldsInGrids($item, $fields, $dottedPrefix)
            ->updateDateFieldsInReplicators($item, $fields, $dottedPrefix)
            ->updateDateFieldsInBard($item, $fields, $dottedPrefix);
    }

    private function updateDateFields($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'date')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                if (
                    $item instanceof EntryContract
                    && $item->collection()->dated()
                    && empty($dottedPrefix)
                    && $field->handle() === 'date'
                ) {
                    $existingDate = $item->date();
                    $localDate = Carbon::parse($existingDate->format('Y-m-d H:i:s'), config('app.timezone'));
                    $convertedDate = $localDate->setTimezone('UTC');

                    $item->date($convertedDate);

                    return;
                }

                $data = $item->data()->all();

                $dottedKey = $dottedPrefix.$field->handle();

                if (! Arr::has($data, $dottedKey)) {
                    return;
                }

                $value = Arr::get($data, $dottedKey);

                $value = $field->get('mode') === 'range'
                    ? $this->processRange($value, $field)
                    : $this->processSingle($value, $field);

                Arr::set($data, $dottedKey, $value);

                $item->data($data);
            });

        return $this;
    }

    private function updateDateFieldsInGroups($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'group')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $this->updateDateFields($item, $field->fieldtype()->fields(), $dottedKey.'.');
            });

        return $this;
    }

    private function updateDateFieldsInGrids($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'grid')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $data = $item->data();
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $rows = Arr::get($data, $dottedKey, []);

                collect($rows)->each(function ($set, $setKey) use ($item, $dottedKey, $field) {
                    $dottedPrefix = "{$dottedKey}.{$setKey}.";
                    $fields = Arr::get($field->config(), 'fields');

                    if ($fields) {
                        $this->recursivelyUpdateFields($item, new Fields($fields), $dottedPrefix);
                    }
                });
            });

        return $this;
    }

    private function updateDateFieldsInReplicators($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'replicator')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $data = $item->data();
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $sets = Arr::get($data, $dottedKey);

                collect($sets)->each(function ($set, $setKey) use ($item, $dottedKey, $field) {
                    $dottedPrefix = "{$dottedKey}.{$setKey}.";
                    $setHandle = Arr::get($set, 'type');
                    $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

                    if ($setHandle && $fields) {
                        $this->recursivelyUpdateFields($item, new Fields($fields), $dottedPrefix);
                    }
                });
            });

        return $this;
    }

    private function updateDateFieldsInBard($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'bard')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $data = $item->data();
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $sets = Arr::get($data, $dottedKey);

                collect($sets)->each(function ($set, $setKey) use ($item, $dottedKey, $field) {
                    $dottedPrefix = "{$dottedKey}.{$setKey}.attrs.values.";
                    $setHandle = Arr::get($set, 'attrs.values.type');
                    $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

                    if ($setHandle && $fields) {
                        $this->recursivelyUpdateFields($item, new Fields($fields), $dottedPrefix);
                    }
                });
            });

        return $this;
    }

    private function processRange(string|array $value, Field $field): array
    {
        if (! is_array($value)) {
            $value = ['start' => $value, 'end' => $value];
        }

        return [
            'start' => $this->processSingle($value['start'], $field),
            'end' => $this->processSingle($value['end'], $field),
        ];
    }

    private function processSingle(int|string $value, Field $field): int|string
    {
        $value = Carbon::parse($value)
            ->utc()
            ->format($field->get('format', $this->defaultFormat($field)));

        if (is_numeric($value)) {
            $value = (int) $value;
        }

        return $value;
    }

    private function defaultFormat(Field $field): string
    {
        return $field->get('time_seconds_enabled')
            ? DateFieldtype::DEFAULT_DATETIME_WITH_SECONDS_FORMAT
            : DateFieldtype::DEFAULT_DATETIME_FORMAT;
    }
}
