<?php

namespace Statamic\Fieldtypes;

use ProseMirrorToHtml\Renderer;
use Statamic\Facades\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\GraphQL\Types\BardSetsType;
use Statamic\GraphQL\Types\BardTextType;
use Statamic\GraphQL\Types\ReplicatorSetType;
use Statamic\Query\Scopes\Filters\Fields\Bard as BardFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Bard extends Replicator
{
    use Concerns\ResolvesStatamicUrls;

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
                    'buttons' => 'contains_any anchor, image',
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
            'link_collections' => [
                'display' => __('Link Collections'),
                'instructions' => __('statamic::fieldtypes.bard.config.link_collections'),
                'type' => 'collections',
                'mode' => 'select',
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
            'enable_input_rules' => [
                'display' => __('Enable Input Rules'),
                'instructions' => __('statamic::fieldtypes.bard.config.enable_input_rules'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'enable_paste_rules' => [
                'display' => __('Enable Paste Rules'),
                'instructions' => __('statamic::fieldtypes.bard.config.enable_paste_rules'),
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
            return is_null($value) ? $value : $this->resolveStatamicUrls($value);
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
            return (new Augmentor($this))->withStatamicImageUrls()->convertToHtml($structure);
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

        $row['attrs']['values'] = Arr::removeNullValues($row['attrs']['values']);

        return $row;
    }

    public function preProcess($value)
    {
        if (empty($value) || $value === '[]') {
            return '[]';
        }

        if (is_string($value)) {
            $value = str_replace('statamic://', '', $value);
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
            return (new Fields($set['fields']))->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all();
        })->all();

        $new = collect($this->config('sets'))->map(function ($set, $handle) use ($defaults) {
            return (new Fields($set['fields']))->addValues($defaults[$handle])->meta()->put('_', '_');
        })->toArray();

        $previews = collect($existing)->map(function ($fields) {
            return collect($fields)->map(function () {
                return null;
            })->all();
        })->all();

        $linkCollections = $this->config('link_collections');

        if (empty($linkCollections)) {
            $site = Site::current()->handle();

            $linkCollections = Blink::once('routable-collection-handles-'.$site, function () use ($site) {
                return Collection::all()->reject(function ($collection) use ($site) {
                    return is_null($collection->route($site));
                })->map->handle()->values();
            });
        }

        return [
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
            'collapsed' => [],
            'previews' => $previews,
            '__collaboration' => ['existing'],
            'linkCollections' => $linkCollections,
            'linkData' => (object) $this->getLinkData($value),
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

    public function toGqlType()
    {
        return $this->config('sets') ? parent::toGqlType() : GraphQL::string();
    }

    public function addGqlTypes()
    {
        $types = collect($this->config('sets'))
            ->each(function ($set, $handle) {
                $this->fields($handle)->all()->each(function ($field) {
                    $field->fieldtype()->addGqlTypes();
                });
            })
            ->map(function ($config, $handle) {
                $type = new ReplicatorSetType($this, $this->gqlSetTypeName($handle), $handle);

                return [
                    'handle' => $handle,
                    'name' => $type->name,
                    'type' => $type,
                ];
            })->values();

        $text = new BardTextType($this);

        $types->push([
            'handle' => 'text',
            'name' => $text->name,
            'type' => $text,
        ]);

        GraphQL::addTypes($types->pluck('type', 'name')->all());

        $union = new BardSetsType($this, $this->gqlSetsTypeName(), $types);

        GraphQL::addType($union);
    }

    public function getLinkData($value)
    {
        return collect($value)->mapWithKeys(function ($node) {
            return $this->extractLinkDataFromNode($node);
        })->all();
    }

    private function extractLinkDataFromNode($node)
    {
        $data = collect();

        if ($node['type'] === 'link') {
            $href = $node['attrs']['href'] ?? null;

            if (Str::startsWith($href, 'statamic://')) {
                $data = $data->merge($this->getLinkDataForUrl($href));
            }
        }

        $childData = collect()
            ->merge($node['content'] ?? [])
            ->merge($node['marks'] ?? [])
            ->mapWithKeys(function ($node) {
                return $this->extractLinkDataFromNode($node);
            });

        return $data->merge($childData);
    }

    private function getLinkDataForUrl($url)
    {
        $ref = Str::after($url, 'statamic://');
        [$type, $id] = explode('::', $ref, 2);

        $data = null;

        switch ($type) {
            case 'entry':
                if ($entry = Entry::find($id)) {
                    $data = [
                        'title' => $entry->get('title'),
                        'permalink' => $entry->absoluteUrl(),
                    ];
                }
                break;
            case 'asset':
                if ($asset = Asset::find($id)) {
                    $data = [
                        'basename' => $asset->basename(),
                        'thumbnail' => $asset->thumbnailUrl(),
                    ];
                }
                break;
        }

        return [$ref => $data];
    }
}
