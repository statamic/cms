<?php

namespace Statamic\Fieldtypes;

use Carbon\Carbon;
use Statamic\Fields\Fieldtype;

class Date extends Fieldtype
{
    protected $configFields = [
        // @TODO hook up multiple mode
        'mode' => [
            'type' => 'select',
            'default' => 'single',
            'instructions' => 'Choose between single or range mode (range disables time picker).',
            'width' => 50,
            'options' => [
                'single' => 'Single',
                // 'multiple' => 'Multiple',
                'range' => 'Range',
            ]
        ],
        'time_enabled'  => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Enable the timepicker.',
            'width' => 50,
        ],
        'time_required' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Require time in addition to date.',
            'width' => 50,
        ],
        'earliest_date' => [
            'type' => 'text',
            'default' => '1900-01-01',
            'instructions' => 'Set the earliest selectable date.',
            'width' => 50,
        ],
        'format' => [
            'type' => 'text',
            'instructions' => 'Optionally format the date string using [moment.js](https://momentjs.com/docs/#/displaying/format/).',
            'width' => 50,
        ],
        'full_width' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Stretch the calender to use up the full width.',
            'width' => 50,
        ],
        'inline' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Skip the dropdown input field and show the calendar directly.',
            'width' => 50,
        ],
        'columns' => [
            'type' => 'integer',
            'default' => 1,
            'instructions' => 'Show multiple months at one time, in rows and columns',
            'width' => 50,
        ],
        'rows' => [
            'type' => 'integer',
            'default' => 1,
            'instructions' => 'Show multiple months at one time, in rows and columns',
            'width' => 50,
        ],
    ];

    public function filterOperators(): array
    {
        return [
            '<' => __('Before'),
            '>' => __('After'),
        ];
    }

    public function preProcess($data)
    {
        if (! $data) {
            return $this->config('required') ? Carbon::now() : null;
        }

        if ($this->config('mode') === "range") {

            // If switching from single to range, all bets are off.
            if (! is_array($data)) {
                return null;
            }

            return [
                'start' => Carbon::parse($data['start'])->format('Y-m-d'),
                'end' => Carbon::parse($data['end'])->format('Y-m-d')
            ];
        }

        // If switching from range mode to single, use the start date.
        if (is_array($data)) {
            $data = array_get($data, 'start', null);
        }

        return Carbon::createFromFormat($this->dateFormat($data), $data)->format('Y-m-d H:i');
    }

    public function process($data)
    {
        if (is_null($data)) return $data;

        if ($this->config('mode') === "range") {
            return [
                'start' => Carbon::parse($data['start'])->format('Y-m-d'),
                'end' => Carbon::parse($data['end'])->format('Y-m-d')
            ];
        }

        $date = Carbon::parse($data);

        return $date->format($this->dateFormat($data));
    }

    private function dateFormat($date)
    {
        return $this->config(
            'format',
            strlen($date) > 10 ? 'Y-m-d H:i' : 'Y-m-d'
        );
    }
}
