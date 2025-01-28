<?php

namespace Statamic\UpdateScripts;

use Carbon\Carbon;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Date as DateFieldtype;
use Statamic\Listeners\Concerns\GetsItemsContainingData;

class ConvertDatesToUtc extends UpdateScript
{
    use GetsItemsContainingData;

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        // TODO: Bail out early if the app's timezone is UTC (and therefore all the dates will already be in UTC)
        // TODO: Improve performance (rather than collecting Repo::all() results, can we use chunking or lazy loading?)

        $this
            ->getItemsContainingData()
            ->filter(fn ($item) => $item instanceof EntryContract) // todo: code needs abstracting for all types of content
            ->each(function ($item) {
                $dateFields = $item->blueprint()->fields()->all()->filter(fn ($field) => $field->type() === 'date');

                if ($dateFields->isEmpty()) {
                    return;
                }

                $dateFields->each(function (Field $field) use ($item) {
                    if (
                        $item instanceof EntryContract
                        && $item->collection()->dated()
                        && $field->handle() === 'date'
                    ) {
                        $format = $this->formatForEntryDateField($field);

                        $item->date($item->date()->setTimezone('UTC')->format($format));

                        return;
                    }

                    $value = $item->get($field->handle());

                    if (! $value) {
                        return;
                    }

                    $value = $field->get('mode') === 'range'
                        ? $this->processRange($value, $field)
                        : $this->processSingle($value, $field);

                    $item->set($field->handle(), $value);
                });

                if ($item->isDirty()) {
                    $item->saveQuietly();
                }
            });
    }

    private function processRange(array $value, Field $field): array
    {
        return [
            'start' => $this->processSingle($value['start'], $field),
            'end' => $this->processSingle($value['end'], $field),
        ];
    }

    private function processSingle(int|string $value, Field $field): int|string
    {
        $value = Carbon::parse($value)
            ->setTimezone('UTC')
            ->format($field->get('format', $this->defaultFormat($field)));

        if (is_numeric($value)) {
            $value = (int) $value;
        }

        return $value;
    }

    private function formatForEntryDateField(Field $field): string
    {
        $format = 'Y-m-d';

        if ($field->get('time_enabled')) {
            $format .= '-Hi';
        }

        if ($field->get('time_seconds_enabled')) {
            $format .= 's';
        }

        return $format;
    }

    private function defaultFormat(Field $field): string
    {
        if ($field->get('time_enabled') && $field->get('mode', 'single') === 'single') {
            return $field->get('time_seconds_enabled')
                ? DateFieldtype::DEFAULT_DATETIME_WITH_SECONDS_FORMAT
                : DateFieldtype::DEFAULT_DATETIME_FORMAT;
        }

        return DateFieldtype::DEFAULT_DATE_FORMAT;
    }
}
