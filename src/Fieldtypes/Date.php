<?php

namespace Statamic\Fieldtypes;

use Carbon\Carbon;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Fields\DateField;
use Statamic\GraphQL\Types\DateRangeType;
use Statamic\Query\Scopes\Filters\Fields\Date as DateFilter;
use Statamic\Statamic;
use Statamic\Support\DateFormat;

class Date extends Fieldtype
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i';

    protected function configFieldItems(): array
    {
        return [
            // @TODO hook up multiple mode
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.date.config.mode'),
                'type' => 'select',
                'default' => 'single',
                'width' => 50,
                'options' => [
                    'single' => __('Single'),
                    // 'multiple' => __('Multiple'),
                    'range' => __('Range'),
                ],
            ],
            'time_enabled'  => [
                'display' => __('Time Enabled'),
                'instructions' => __('statamic::fieldtypes.date.config.time_enabled'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'time_required' => [
                'display' => __('Time Required'),
                'instructions' => __('statamic::fieldtypes.date.config.time_required'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'earliest_date' => [
                'display' => __('Earliest Date'),
                'instructions' => __('statamic::fieldtypes.date.config.earliest_date'),
                'type' => 'text',
                'default' => '1900-01-01',
                'width' => 50,
            ],
            'format' => [
                'display' => __('Format'),
                'instructions' => __('statamic::fieldtypes.date.config.format'),
                'type' => 'text',
                'width' => 50,
            ],
            'full_width' => [
                'display' => __('Full Width'),
                'instructions' => __('statamic::fieldtypes.date.config.full_width'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.date.config.inline'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'columns' => [
                'display' => __('Columns'),
                'instructions' => __('statamic::fieldtypes.date.config.columns'),
                'type' => 'integer',
                'default' => 1,
                'width' => 50,
            ],
            'rows' => [
                'display' => __('Rows'),
                'instructions' => __('statamic::fieldtypes.date.config.rows'),
                'type' => 'integer',
                'default' => 1,
                'width' => 50,
            ],
        ];
    }

    public function filter()
    {
        return new DateFilter($this);
    }

    public function preProcess($data)
    {
        return $this->config('mode') == 'range' ? $this->preProcessRange($data) : $this->preProcessSingle($data);
    }

    private function preProcessSingle($value)
    {
        $vueFormat = $this->config('time_enabled') ? self::DEFAULT_DATETIME_FORMAT : self::DEFAULT_DATE_FORMAT;

        if (! $value) {
            return $this->isRequired() ? Carbon::now()->format($vueFormat) : null;
        }

        // If the value is an array, this field probably used to be a range. In this case, we'll use the start date.
        if (is_array($value)) {
            $value = $value['start'];
        }

        $date = Carbon::createFromFormat($this->saveFormat($value), $value);

        return $date->format($vueFormat);
    }

    private function preProcessRange($value)
    {
        $vueFormat = self::DEFAULT_DATE_FORMAT;

        if (! $value) {
            return $this->isRequired() ? [
                'start' => Carbon::now()->format($vueFormat),
                'end' => Carbon::now()->format($vueFormat),
            ] : null;
        }

        // If the value is a string, this field probably used to be a single date.
        // In this case, we'll use the date for both the start and end of the range.
        if (is_string($value)) {
            $value = ['start' => $value, 'end' => $value];
        }

        return [
            'start' => Carbon::createFromFormat($this->saveFormat($value['start']), $value['start'])->format($vueFormat),
            'end' => Carbon::createFromFormat($this->saveFormat($value['end']), $value['end'])->format($vueFormat),
        ];
    }

    private function isRequired()
    {
        return in_array('required', $this->field->rules()[$this->field->handle()]);
    }

    public function process($data)
    {
        return $this->config('mode') == 'range' ? $this->processRange($data) : $this->processSingle($data);
    }

    private function processSingle($data)
    {
        if (is_null($data)) {
            return $data;
        }

        $date = Carbon::parse($data);

        return $this->formatAndCast($date, $this->saveFormat($data));
    }

    private function processRange($data)
    {
        if (is_null($data)) {
            return $data;
        }

        return [
            'start' => $this->processSingle($data['start']),
            'end' => $this->processSingle($data['end']),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        if ($this->config('mode') === 'range') {
            $start = Carbon::parse($data['start'])->format($this->indexDisplayFormat());
            $end = Carbon::parse($data['end'])->format($this->indexDisplayFormat());

            return $start.' - '.$end;
        }

        return Carbon::parse($data)->format($this->indexDisplayFormat());
    }

    private function saveFormat($date)
    {
        return $this->config(
            'format',
            strlen($date) > 10 ? self::DEFAULT_DATETIME_FORMAT : self::DEFAULT_DATE_FORMAT
        );
    }

    public function indexDisplayFormat()
    {
        if (! $this->config('time_enabled')) {
            return Statamic::cpDateFormat();
        }

        return $this->config('format') || strlen($this->field->value()) > 10
            ? Statamic::cpDateTimeFormat()
            : Statamic::cpDateFormat();
    }

    public function fieldDisplayFormat()
    {
        return Statamic::cpDateFormat();
    }

    private function formatAndCast(Carbon $date, $format)
    {
        $formatted = $date->format($format);

        if (is_numeric($formatted)) {
            $formatted = (int) $formatted;
        }

        return $formatted;
    }

    public function preload()
    {
        return [
            'displayFormat' => DateFormat::toIso($this->fieldDisplayFormat()),
        ];
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($this->config('mode') === 'range') {
            return [
                'start' => Carbon::createFromFormat($this->saveFormat($value['start']), $value['start'])->startOfDay(),
                'end' => Carbon::createFromFormat($this->saveFormat($value['end']), $value['end'])->startOfDay(),
            ];
        }

        $date = Carbon::createFromFormat($this->saveFormat($value), $value);

        // Make sure that if it was only a date saved, then the time would be reset
        // to the beginning of the day, otherwise it would inherit the hour and
        // minute from the current time. If they've defined a custom format
        // we'll skip this since it'll already be what they wanted.
        if (! $this->config('format') && strlen($value) === 10) {
            $date->startOfDay();
        }

        return $date;
    }

    public function toGqlType()
    {
        if ($this->config('mode') === 'range') {
            return GraphQL::type(DateRangeType::NAME);
        }

        return new DateField;
    }
}
