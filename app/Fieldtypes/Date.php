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
        // @TODO hook up multiple and range modes
        'mode' => [
            'type' => 'select',
            'default' => 'single',
            'options' => [
                ['text' => 'Single', 'value' => 'single'],
                ['text' => 'Multiple', 'value' => 'multiple'],
                ['text' => 'Range', 'value' => 'range'],
            ]
        ]
    ];

    public function preProcess($data)
    {
        if (! $data) {
            return;
        }

        return Carbon::createFromFormat($this->dateFormat($data), $data)->format('Y-m-d H:i');
    }

    public function process($data)
    {
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
