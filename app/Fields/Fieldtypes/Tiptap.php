<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtypes\Bard\Augmentor;

class Tiptap extends Replicator
{
    public $category = ['text', 'structured'];
    protected $configFields = [
        'sets' => ['type' => 'sets'],
    ];

    public function augment($value)
    {
        return (new Augmentor($this->config()))->augment($value);
    }

    public function process($value)
    {
        $value = json_decode($value, true);

        return collect($value)->map(function ($row) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->processRow($row);
        })->all();
    }

    protected function processRow($row)
    {
        $processed = parent::processRow($row['attrs']['values']);

        return [
            'type' => 'set',
            'attrs' => [
                'values' => $processed
            ]
        ];
    }

    public function preProcess($value)
    {
        return collect($value)->map(function ($row) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->preProcessRow($row);
        })->all();
    }

    protected function preProcessRow($row)
    {
        $processed = parent::preProcessRow($row['attrs']['values']);

        return [
            'type' => 'set',
            'attrs' => [
                'values' => $processed,
            ]
        ];
    }
}