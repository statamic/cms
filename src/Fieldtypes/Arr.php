<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Arr extends Fieldtype
{
    protected static $handle = 'array';

    protected $configFields = [
        'mode' => [
            'type' => 'radio',
            'options' => [
                'dynamic' => 'Dynamic',
                'keyed' => 'Keyed'
            ],
            'default' => 'dynamic'
        ],
        'keys' => [
            'type' => 'array',
            'value_header' => 'Label (optional)',
            'instructions' => 'Set the array keys (variables) and optional labels.',
            'if' => [
                'mode' => 'keyed'
            ]
        ],
    ];

    public function preProcess($data)
    {
        return array_merge($this->blankKeyed(), $data ?? []);
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
