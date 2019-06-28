<?php

namespace Statamic\Fieldtypes;

use Carbon\Carbon;
use Statamic\Fields\Fieldtype;

class Date extends Fieldtype
{
    protected $configFields = [
        'time_enabled'  => [
            'type' => 'toggle',
            'default' => false
        ],
        'time_required' => [
            'type' => 'toggle',
            'default' => false
        ],
        'earliest_date' => [
            'type' => 'text',
            'default' => '1900-01-01'
        ],
        'format' => ['type' => 'text'],
        'full_width' => [
            'type' => 'toggle',
            'default' => false
        ],
        'inline' => [
            'type' => 'toggle',
            'default' => false
        ],
        'columns' => [
            'type' => 'integer',
            'default' => 1
        ],
        'rows' => [
            'type' => 'integer',
            'default' => 1
        ],
        // @TODO hook up multiple mode
        'mode' => [
            'type' => 'select',
            'default' => 'single',
            'options' => [
                'single' => 'Single',
                // 'multiple' => 'Multiple',
                'range' => 'Range',
            ]
        ]
    ];

    public function preProcess($data)
    {
        if (! $data) {
            return;
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
