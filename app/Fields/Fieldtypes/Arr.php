<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Arr extends Fieldtype
{
    protected static $handle = 'array';

    protected $configFields = [
        'keys' => [
            'type' => 'array'
        ]
    ];

    public function preProcess($data)
    {
        return array_merge($this->blankKeyed(), $data ?? []);
    }

    public function preProcessConfig($data)
    {
        return format_input_options($data);
    }

    public function process($data)
    {
        return collect($data)
            ->when($this->isKeyed(), function ($data) {
                return $data->filter();
            })
            ->all();
    }

    protected function isKeyed()
    {
        return (bool) $this->config('keys');
    }

    protected function blankKeyed()
    {
        return collect($this->config('keys'))
            ->map(function () {
                return null;
            })
            ->all();
    }
}
