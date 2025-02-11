<?php

namespace Statamic\Fieldtypes;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Fields\DateField;
use Statamic\GraphQL\Types\DateRangeType;
use Statamic\Query\Scopes\Filters\Fields\Date as DateFilter;
use Statamic\Rules\DateFieldtype as ValidationRule;
use Statamic\Statamic;
use Statamic\Support\DateFormat;

class Date extends Fieldtype
{
    protected $categories = ['special'];
    protected $keywords = ['datetime', 'time'];

    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i';
    const DEFAULT_DATETIME_WITH_SECONDS_FORMAT = 'Y-m-d H:i:s';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.date.config.mode'),
                        'type' => 'select',
                        'default' => 'single',
                        'options' => [
                            'single' => __('Single'),
                            // 'multiple' => __('Multiple'), // @TODO hook up
                            'range' => __('Range'),
                        ],
                    ],
                    'inline' => [
                        'display' => __('Inline'),
                        'instructions' => __('statamic::fieldtypes.date.config.inline'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'full_width' => [
                        'display' => __('Full Width'),
                        'instructions' => __('statamic::fieldtypes.date.config.full_width'),
                        'type' => 'toggle',
                        'default' => false,
                        'if' => [
                            'inline' => true,
                        ],
                    ],
                    'columns' => [
                        'display' => __('Columns'),
                        'instructions' => __('statamic::fieldtypes.date.config.columns'),
                        'type' => 'integer',
                        'default' => 1,
                    ],
                    'rows' => [
                        'display' => __('Rows'),
                        'instructions' => __('statamic::fieldtypes.date.config.rows'),
                        'type' => 'integer',
                        'default' => 1,
                    ],
                ],
            ],
            [
                'display' => __('Timepicker'),
                'fields' => [
                    'time_enabled' => [
                        'display' => __('Time Enabled'),
                        'instructions' => __('statamic::fieldtypes.date.config.time_enabled'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'time_seconds_enabled' => [
                        'display' => __('Show Seconds'),
                        'instructions' => __('statamic::fieldtypes.date.config.time_seconds_enabled'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                ],
            ],
            [
                'display' => __('Boundaries'),
                'fields' => [
                    'earliest_date' => [
                        'display' => __('Earliest Date'),
                        'instructions' => __('statamic::fieldtypes.date.config.earliest_date'),
                        'type' => 'date',
                    ],
                    'latest_date' => [
                        'display' => __('Latest Date'),
                        'instructions' => __('statamic::fieldtypes.date.config.latest_date'),
                        'type' => 'date',
                    ],
                ],
            ],
            [
                'display' => __('Data Format'),
                'fields' => [
                    'format' => [
                        'display' => __('Format'),
                        'instructions' => __('statamic::fieldtypes.date.config.format'),
                        'type' => 'text',
                    ],
                ],
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
        if (! $value) {
            return ['date' => null, 'time' => null];
        }

        if ($value === 'now') {
            return [
                // We want the current date and time to be rendered, but since we don't
                // know the users timezone, we'll let the front-end handle it.
                'date' => now()->startOfDay()->format(self::DEFAULT_DATE_FORMAT),
                'time' => $this->config('time_enabled') ? 'now' : null, // This will get replaced with the current time in Vue component.
            ];
        }

        // If the value is an array, this field probably used to be a range. In this case, we'll use the start date.
        if (is_array($value)) {
            $value = $value['start'];
        }

        $date = $this->parseSaved($value);

        return $this->splitDateTimeForPreProcessSingle($date);
    }

    private function preProcessRange($value)
    {
        $vueFormat = $this->defaultFormat();

        if (! $value) {
            return $this->splitDateTimeForPreProcessRange($this->isRequired() ? [
                'start' => Carbon::now()->format($vueFormat),
                'end' => Carbon::now()->format($vueFormat),
            ] : null);
        }

        // If the value is a string, this field probably used to be a single date.
        // In this case, we'll use the date for both the start and end of the range.
        if (is_string($value)) {
            $value = ['start' => $value, 'end' => $value];
        }

        return $this->splitDateTimeForPreProcessRange([
            'start' => $this->parseSaved($value['start'])->format($vueFormat),
            'end' => $this->parseSaved($value['end'])->format($vueFormat),
        ]);
    }

    private function splitDateTimeForPreProcessSingle(Carbon $carbon)
    {
        return [
            'date' => $carbon->format(self::DEFAULT_DATE_FORMAT),
            'time' => $this->config('time_enabled')
                ? $carbon->format($this->config('time_seconds_enabled') ? 'H:i:s' : 'H:i')
                : null,
        ];
    }

    private function splitDateTimeForPreProcessRange(?array $range = null)
    {
        return ['date' => $range, 'time' => null];
    }

    public function isRequired()
    {
        return in_array('required', $this->field->rules()[$this->field->handle()]);
    }

    public function process($data)
    {
        if (is_null($data) || is_null($data['date'])) {
            return null;
        }

        return $this->config('mode') == 'range' ? $this->processRange($data) : $this->processSingle($data);
    }

    private function processSingle($data)
    {
        return $this->processDateTime($data['date'].' '.($data['time'] ?? '00:00'));
    }

    private function processRange($data)
    {
        $date = $data['date'];

        return [
            'start' => $this->processDateTime($date['start']),
            'end' => $this->processDateTimeEndOfDay($date['end']),
        ];
    }

    private function processDateTime($value)
    {
        $date = Carbon::parse($value);

        return $this->formatAndCast($date, $this->saveFormat());
    }

    private function processDateTimeEndOfDay($value)
    {
        $date = Carbon::parse($value)->endOfDay();

        return $this->formatAndCast($date, $this->saveFormat());
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return;
        }

        if ($this->config('mode') === 'range') {
            // If the value is a string, this field probably used to be a single date.
            // In this case, we'll use the date for both the start and end of the range.
            if (is_string($value)) {
                $value = ['start' => $value, 'end' => $value];
            }

            $start = $this->parseSaved($value['start'])->format($this->indexDisplayFormat());
            $end = $this->parseSaved($value['end'])->format($this->indexDisplayFormat());

            return $start.' - '.$end;
        }

        // If the value is an array, this field probably used to be a range. In this case, we'll use the start date.
        if (is_array($value)) {
            $value = $value['start'];
        }

        return $this->parseSaved($value)->format($this->indexDisplayFormat());
    }

    private function saveFormat()
    {
        return $this->config('format', $this->defaultFormat());
    }

    public function indexDisplayFormat()
    {
        return $this->config('time_enabled') && $this->config('mode', 'single') === 'single'
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
            return Carbon::createFromFormat($this->saveFormat(), $value, 'UTC');
        } catch (InvalidFormatException|InvalidArgumentException $e) {
            return Carbon::parse($value);
        }
    }

    public function timeEnabled()
    {
        return $this->config('time_enabled');
    }

    public function secondsEnabled()
    {
        return $this->config('time_seconds_enabled');
    }

    public function preProcessValidatable($value)
    {
        Validator::make(
            [$this->field->handle() => $value],
            [$this->field->handle() => [new ValidationRule($this)]],
        )->validate();

        if ($value === null) {
            return null;
        }

        if ($this->config('mode', 'single') === 'single') {
            return $this->preProcessSingleValidatable($value);
        }

        if (isset($value['start'])) {
            // It was already processed.
            return $value;
        }

        return $this->preProcessRangeValidatable($value['date']);
    }

    private function preProcessSingleValidatable($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (! $value['date']) {
            return null;
        }

        $time = $value['time'] ?? '00:00';

        if (substr_count($time, ':') === 1) {
            $time .= ':00';
        }

        return Carbon::createFromFormat(self::DEFAULT_DATETIME_WITH_SECONDS_FORMAT, $value['date'].' '.$time);
    }

    private function preProcessRangeValidatable($value)
    {
        if (! isset($value['start'])) {
            return null;
        }

        return [
            'start' => Carbon::createFromFormat(self::DEFAULT_DATE_FORMAT, $value['start'])->startOfDay(),
            'end' => Carbon::createFromFormat(self::DEFAULT_DATE_FORMAT, $value['end'])->startOfDay(),
        ];
    }
}
