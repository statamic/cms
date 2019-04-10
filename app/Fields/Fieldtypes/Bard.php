<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtypes\Bard\Augmentor;

class Bard extends Replicator
{
    public $category = ['text', 'structured'];

    protected $configFields = [
        'sets' => ['type' => 'sets'],
        'save_html' => ['type' => 'toggle'],
    ];

    public function augment($value)
    {
        return (new Augmentor)->augment($value);
    }

    public function process($value)
    {
        $value = json_decode($value, true);

        $structure = collect($value)->map(function ($row) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->processRow($row);
        })->all();

        if ($this->shouldSaveHtml()) {
            return (new Augmentor)->convertToHtml($structure);
        }

        return $structure;
    }

    protected function shouldSaveHtml()
    {
        if ($this->config('sets')) {
            return false;
        }

        return $this->config('save_html');
    }

    protected function processRow($row)
    {
        $row['attrs']['values'] = parent::processRow($row['attrs']['values']);

        if ($row['attrs']['enabled'] === true) {
            unset($row['attrs']['enabled']);
        }

        return $row;
    }

    public function preProcess($value)
    {
        if (is_string($value)) {
            $doc = (new \Scrumpy\HtmlToProseMirror\Renderer)->render($value);
            $value = $doc['content'];
        }

        return collect($value)->map(function ($row) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->preProcessRow($row);
        })->toJson();
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

    public function extraRules(): array
    {
        if (! $this->config('sets')) {
            return [];
        }

        return parent::extraRules();
    }
}