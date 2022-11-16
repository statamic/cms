<?php

namespace Statamic\Fieldtypes;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Fields\DateField;
use Statamic\GraphQL\Types\DateRangeType;
use Statamic\Query\Scopes\Filters\Fields\Date as DateFilter;
use Statamic\Statamic;
use Statamic\Support\DateFormat;

class Date extends Fieldtype
{
    protected $categories = ['special'];

    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i';
    const DEFAULT_DATETIME_WITH_SECONDS_FORMAT = 'Y-m-d H:i:s';

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
            'format' => [
                'display' => __('Format'),
                'instructions' => __('statamic::fieldtypes.date.config.format'),
                'type' => 'text',
                'width' => 50,
            ],
            'earliest_date' => [
                'display' => __('Earliest Date'),
                'instructions' => __('statamic::fieldtypes.date.config.earliest_date'),
                'type' => 'date',
                'width' => 50,
            ],
            'latest_date' => [
                'display' => __('Latest Date'),
                'instructions' => __('statamic::fieldtypes.date.config.latest_date'),
                'type' => 'date',
                'width' => 50,
            ],
            'time_enabled'  => [
                'display' => __('Time Enabled'),
                'instructions' => __('statamic::fieldtypes.date.config.time_enabled'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'time_seconds_enabled'  => [
                'display' => __('Show Seconds'),
                'instructions' => __('statamic::fieldtypes.date.config.time_seconds_enabled'),
                'type' => 'toggle',
                'default' => false,
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
        $vueFormat = $this->defaultFormat();

        if (! $value) {
            return $this->isRequired() ? Carbon::now()->format($vueFormat) : null;
        }

        // If the value is an array, this field probably used to be a range. In this case, we'll use the start date.
        if (is_array($value)) {
            $value = $value['start'];
        }

        $date = $this->parseSaved($value);

        return $date->format($vueFormat);
    }

    private function preProcessRange($value)
    {
        $vueFormat = $this->defaultFormat();

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
            'start' => $this->parseSaved($value['start'])->format($vueFormat),
            'end' => $this->parseSaved($value['end'])->format($vueFormat),
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

        return $this->formatAndCast($date, $this->saveFormat());
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

        return $this->parseSaved($data)->format($this->indexDisplayFormat());
    }

    private function saveFormat()
    {
        return $this->config('format', $this->defaultFormat());
    }

    public function indexDisplayFormat()
    {
        return $this->config('time_enabled')
            ? Statamic::cpDateTimeFormat()
            : Statamic::cpDateFormat();
    }

    public function fieldDisplayFormat()
    {
        return Statamic::cpDateFormat();
    }

    private function defaultFormat()
    {
        if ($this->config('time_enabled') && $this->config('mode', 'single') === 'single') {
            return $this->config('time_seconds_enabled')
                ? self::DEFAULT_DATETIME_WITH_SECONDS_FORMAT
                : self::DEFAULT_DATETIME_FORMAT;
        }

        return self::DEFAULT_DATE_FORMAT;
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
                'start' => $this->parseSaved($value['start'])->startOfDay(),
                'end' => $this->parseSaved($value['end'])->startOfDay(),
            ];
        }

        $date = $this->parseSaved($value);

        if (! $this->config('time_enabled')) {
            $date->startOfDay();
        } elseif (! $this->config('time_seconds_enabled')) {
            $date->startOfMinute();
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

    public function toQueryableValue($value)
    {
        return $this->augment($value);
    }

    private function parseSaved($value)
    {
        try {
            return Carbon::createFromFormat($this->saveFormat(), $value);
        } catch (InvalidFormatException|InvalidArgumentException $e) {
            return Carbon::parse($value);
        }
    }
}
