<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Arr extends Fieldtype
{
    protected static $handle = 'array';

    protected function configFieldItems(): array
    {
        return [
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.array.config.mode'),
                'type' => 'radio',
                'default' => 'dynamic',
                'options' => [
                    'dynamic' => __('Dynamic'),
                    'keyed' => __('Keyed'),
                ],
            ],
            'keys' => [
                'display' => __('Keys'),
                'instructions' => __('statamic::fieldtypes.array.config.keys'),
                'type' => 'array',
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'if' => [
                    'mode' => 'keyed',
                ],
            ],
        ];
    }

    public function preProcess($data)
    {
        return array_replace($this->blankKeyed(), $data ?? []);
    }

    public function preProcessConfig($data)
    {
        return $data ?? [];
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
