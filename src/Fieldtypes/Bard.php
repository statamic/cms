<?php

namespace Statamic\Fieldtypes;

use GraphQL\Type\Definition\Type;
use ProseMirrorToHtml\Renderer;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\GraphQL\Types\BardSetsType;
use Statamic\GraphQL\Types\BardTextType;
use Statamic\GraphQL\Types\ReplicatorSetType;
use Statamic\Query\Scopes\Filters\Fields\Bard as BardFilter;
use Statamic\Support\Arr;

class Bard extends Replicator
{
    public $category = ['text', 'structured'];
    protected $defaultValue = '[]';

    protected function configFieldItems(): array
    {
        return [
            'always_show_set_button' => [
                'display' => __('Always Show Set Button'),
                'instructions' => __('statamic::fieldtypes.bard.config.always_show_set_button'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'sets' => [
                'display' => __('Sets'),
                'instructions' => __('statamic::fieldtypes.bard.config.sets'),
                'type' => 'sets',
                'require_section' => false,
            ],
            'buttons' => [
                'display' => __('Buttons'),
                'instructions' => __('statamic::fieldtypes.bard.config.buttons'),
                'type' => 'bard_buttons_setting',
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
                    'table',
                ],
            ],
            'container' => [
                'display' => __('Container'),
                'instructions' => __('statamic::fieldtypes.bard.config.container'),
                'type' => 'asset_container',
                'max_items' => 1,
                'if' => [
                    'buttons' => 'contains image',
                ],
            ],
            'save_html' => [
                'display' => __('Display HTML'),
                'instructions' => __('statamic::fieldtypes.bard.config.save_html'),
                'type' => 'toggle',
            ],
            'toolbar_mode' => [
                'display' => __('Toolbar Mode'),
                'instructions' => __('statamic::fieldtypes.bard.config.toolbar_mode'),
                'type' => 'select',
                'default' => 'fixed',
                'options' => [
                    'fixed' => __('Fixed'),
                    'floating' => __('Floating'),
                ],
                'width' => 50,
            ],
            'link_noopener' => [
                'display' => __('Link Noopener'),
                'instructions' => __('statamic::fieldtypes.bard.config.link_noopener'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'link_noreferrer' => [
                'display' => __('Link Noreferrer'),
                'instructions' => __('statamic::fieldtypes.bard.config.link_noreferrer'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'target_blank' => [
                'display' => __('Target Blank'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
                'instructions' => __('statamic::fieldtypes.bard.config.target_blank'),
            ],
            'reading_time' => [
                'display' => __('Show Reading Time'),
                'instructions' => __('statamic::fieldtypes.bard.config.reading_time'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'fullscreen' => [
                'display' => __('Allow Fullscreen Mode'),
                'instructions' => __('statamic::fieldtypes.bard.config.fullscreen'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'allow_source' => [
                'display' => __('Allow Source Mode'),
                'instructions' => __('statamic::fieldtypes.bard.config.allow_source'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
        ];
    }

    public function filter()
    {
        return new BardFilter($this);
    }

    protected function performAugmentation($value, $shallow)
    {
        if ($this->shouldSaveHtml()) {
            return $value;
        }

        if ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
        }

        return (new Augmentor($this))->augment($value, $shallow);
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

        if ($structure === [['type' => 'paragraph']]) {
            return null;
        }

        if ($structure === []) {
            return null;
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
        if (empty($value) || $value === '[]') {
            return '[]';
        }

        if (is_string($value)) {
            $doc = (new \HtmlToProseMirror\Renderer)->render($value);
            $value = $doc['content'];
        } elseif ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
        }

        return collect($value)->map(function ($row, $i) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->preProcessRow($row, $i);
        })->toJson();
    }

    protected function preProcessRow($row, $index)
    {
        $values = parent::preProcessRow($row['attrs']['values'], $index);

        unset($values['_id']);

        return [
            'type' => 'set',
            'attrs' => [
                'id' => "set-$index",
                'enabled' => $row['attrs']['enabled'] ?? true,
                'values' => Arr::except($values, 'enabled'),
            ],
        ];
    }

    public function preProcessIndex($value)
    {
        if (is_string($value)) {
            return $value;
        }

        if ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
        }

        $data = collect($value)->reject(function ($value) {
            return $value['type'] === 'set';
        })->values();

        $renderer = new Renderer;

        return $renderer->render([
            'type' => 'doc',
            'content' => $data,
        ]);
    }

    public function extraRules(): array
    {
        if (! $this->config('sets')) {
            return [];
        }

        return collect($this->field->value())->filter(function ($set) {
            return $set['type'] === 'set';
        })->map(function ($set, $index) {
            $set = $set['attrs']['values'];

            return $this->setRules($set['type'], $set, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    protected function setRuleFieldKey($handle, $index)
    {
        return "{$this->field->handle()}.{$index}.attrs.values.{$handle}";
    }

    public function isLegacyData($value)
    {
        if (is_string($value)) {
            return false;
        }

        if (! $setConfig = $this->config('sets')) {
            return false;
        }

        $configuredTypes = array_keys($setConfig);
        $configuredTypes[] = 'text';
        $dataTypes = collect($value)->map->type;

        return $dataTypes->diff($configuredTypes)->count() === 0;
    }

    protected function convertLegacyData($value)
    {
        return collect($value)->flatMap(function ($set, $i) {
            if ($set['type'] === 'text') {
                if (empty($set['text'])) {
                    return;
                }
                $doc = (new \HtmlToProseMirror\Renderer)->render($set['text']);

                return $doc['content'];
            }

            return [
                [
                    'type' => 'set',
                    'attrs' => [
                        'id' => "set-$i",
                        'values' => $set,
                    ],
                ],
            ];
        })->all();
    }

    public function preload()
    {
        $value = json_decode($this->field->value(), true);

        $existing = collect($value)->filter(function ($item) {
            return $item['type'] === 'set';
        })->mapWithKeys(function ($set) {
            $values = $set['attrs']['values'];
            $config = $this->config("sets.{$values['type']}.fields", []);

            return [$set['attrs']['id'] => (new Fields($config))->addValues($values)->meta()->put('_', '_')];
        })->toArray();

        $defaults = collect($this->config('sets'))->map(function ($set) {
            return (new Fields($set['fields']))->all()->map->defaultValue()->all();
        })->all();

        $new = collect($this->config('sets'))->map(function ($set, $handle) use ($defaults) {
            return (new Fields($set['fields']))->addValues($defaults[$handle])->meta()->put('_', '_');
        })->toArray();

        $previews = collect($existing)->map(function ($fields) {
            return collect($fields)->map(function () {
                return null;
            })->all();
        })->all();

        return [
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
            'collapsed' => [],
            'previews' => $previews,
            '__collaboration' => ['existing'],
        ];
    }

    public function preProcessValidatable($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $value = json_decode($value, true);

        return collect($value)->map(function ($item) {
            if ($item['type'] !== 'set') {
                return $item;
            }

            $values = $item['attrs']['values'];

            $processed = $this->fields($values['type'])
                ->addValues($values)
                ->preProcessValidatables()
                ->values()
                ->all();

            $item['attrs']['values'] = array_merge($values, $processed);

            return $item;
        })->all();
    }

    public function graphQlType(): Type
    {
        return $this->config('sets') ? parent::graphQlType() : Type::string();
    }

    public function addGqlTypes()
    {
        $types = collect($this->config('sets'))->mapWithKeys(function ($config, $handle) {
            $type = new ReplicatorSetType($this, $handle);

            return [$type->name => $type];
        });

        $text = new BardTextType($this);

        $types->put($text->name, $text);

        GraphQL::addTypes($types->all());

        $union = new BardSetsType($this, $types->map(function ($type, $name) {
            return GraphQL::type($name);
        })->all());

        GraphQL::addType($union);
    }
}
