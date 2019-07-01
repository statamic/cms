<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fields;
use Scrumpy\ProseMirrorToHtml\Renderer;
use Statamic\Fieldtypes\Bard\Augmentor;

class Bard extends Replicator
{
    public $category = ['text', 'structured'];

    protected $configFields = [
        'sets' => ['type' => 'sets'],
        'buttons' => [
            'type' => 'bard_buttons_setting',
            'instructions' => 'Choose which buttons to show in the toolbar.',
            'default' => [
                'h2',
                'h3',
                'bold',
                'italic',
                'unorderedlist',
                'orderedlist',
                'removeformat',
                'quote',
                'anchor',
                'image',
            ]
        ],
        'save_html' => [
            'type' => 'toggle',
            'display' => 'Save HTML',
            'instructions' => 'Save HTML instead of structured data. This simplifies but limits control of your template markup.'
        ],
        'toolbar_mode' => [
            'type' => 'select',
            'default' => 'fixed',
            'options' => [
                'fixed' => 'Fixed',
                'floating' => 'Floating',
            ],
            'instructions' => 'Choose which style of toolbar you prefer.'
        ],
        'link_noopener' => [
            'type' => 'toggle',
            'default' => false,
            'width' => 50,
            'instructions' => 'Set `rel="noopener` on all links.'
        ],
        'link_noreferrer' => [
            'type' => 'toggle',
            'default' => false,
            'width' => 50,
            'instructions' => 'Set `rel="noreferrer` on all links.'
        ],
        'target_blank' => [
            'type' => 'toggle',
            'default' => false,
            'width' => 50,
            'instructions' => 'Set `target="_blank` on all links.'
        ],
        'reading_time' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Show estimated reading time at the bottom of the field.'
        ],
        'fullscreen' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enable the option to toggle into fullscreen mode'
        ],
        'allow_source' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enable the option to view the HTML source code while writing.'
        ]
    ];

    public function augment($value)
    {
        if ($this->field->handle() == "content") {
            throw new \Exception("The fieldname `content` is reserved for string-based fieldtypes.");
        }

        if ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
        }

        return (new Augmentor($this))->augment($value);
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
            return (new Augmentor($this))->convertToHtml($structure);
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

        unset($row['attrs']['id']);

        if (array_get($row, 'attrs.enabled', true) === true) {
            unset($row['attrs']['enabled']);
        }

        return $row;
    }

    public function preProcess($value)
    {
        if (empty($value)) {
            return '[]';
        }

        if (is_string($value)) {
            $doc = (new \Scrumpy\HtmlToProseMirror\Renderer)->render($value);
            $value = $doc['content'];
        } else if ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
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

    public function preProcessIndex($value)
    {
        $data = collect($value)->reject(function ($value) {
            return $value['type'] === 'set';
        });

        $renderer = new Renderer;

        return $renderer->render([
            'type' => 'doc',
            'content' => $data
        ]);
    }

    public function extraRules(): array
    {
        if (! $this->config('sets')) {
            return [];
        }

        return parent::extraRules();
    }

    public function isLegacyData($value)
    {
        $configuredTypes = array_keys($this->config('sets', []));
        $configuredTypes[] = 'text';
        $dataTypes = collect($value)->map->type;

        return $dataTypes->diff($configuredTypes)->count() === 0;
    }

    protected function convertLegacyData($value)
    {
        return collect($value)->flatMap(function ($set) {
            if ($set['type'] === 'text') {
                $doc = (new \Scrumpy\HtmlToProseMirror\Renderer)->render($set['text']);
                return $doc['content'];
            }

            return [
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => $set,
                    ]
                ]
            ];
        })->all();
    }

    public function preload()
    {
        $value = json_decode($this->field->value(), true);

        $existing = collect($value)->filter(function ($item) {
            return $item['type'] === 'set';
        })->map(function ($set) {
            $values = $set['attrs']['values'];
            $config = $this->config("sets.{$values['type']}.fields", []);
            return (new Fields($config))->addValues($values)->meta();
        })->toArray();

        $defaults = collect($this->config('sets'))->map(function ($set) {
            return (new Fields($set['fields']))->all()->map->defaultValue()->all();
        })->all();

        $new = collect($this->config('sets'))->map(function ($set, $handle) use ($defaults) {
            return (new Fields($set['fields']))->addValues($defaults[$handle])->meta();
        })->toArray();

        return compact('existing', 'new', 'defaults');
    }
}
