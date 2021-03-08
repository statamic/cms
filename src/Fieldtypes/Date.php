<?php

namespace Statamic\Fieldtypes;

use Carbon\Carbon;
use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Date as DateFilter;

class Date extends Fieldtype
{
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
        if (! $data) {
            return $this->config('required') ? Carbon::now() : null;
        }

        if ($this->config('mode') === 'range') {

            // If switching from single to range, all bets are off.
            if (! is_array($data)) {
                return null;
            }

            return [
                'start' => Carbon::parse($data['start'])->format('Y-m-d'),
                'end' => Carbon::parse($data['end'])->format('Y-m-d'),
            ];
        }

        // If switching from range mode to single, use the start date.
        if (is_array($data)) {
            $data = array_get($data, 'start', null);
        }

        return Carbon::createFromFormat($this->dateFormat($data), $data)->format($this->config('time_enabled') ? 'Y-m-d H:i' : 'Y-m-d');
    }

    public function process($data)
    {
        if (is_null($data)) {
            return $data;
        }

        if ($this->config('mode') === 'range') {
            return $data;
        }

        $date = Carbon::parse($data);

        return $date->format($this->dateFormat($data));
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        if ($this->config('mode') === 'range') {
            $start = Carbon::parse($data['start'])->format(config('statamic.cp.date_format'));
            $end = Carbon::parse($data['end'])->format(config('statamic.cp.date_format'));

            return $start.' - '.$end;
        }

        return Carbon::parse($data)->format(config('statamic.cp.date_format'));
    }

    private function dateFormat($date)
    {
        return $this->config(
            'format',
            strlen($date) > 10 ? 'Y-m-d H:i' : 'Y-m-d'
        );
    }
}
