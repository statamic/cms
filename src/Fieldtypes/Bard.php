<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\GraphQL\Types\BardSetsType;
use Statamic\GraphQL\Types\BardTextType;
use Statamic\GraphQL\Types\ReplicatorSetType;
use Statamic\Query\Scopes\Filters\Fields\Bard as BardFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;

class Bard extends Replicator
{
    use Concerns\ResolvesStatamicUrls, Hookable;

    private static $defaultButtons = [
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
    ];

    protected $categories = ['text', 'structured'];
    protected $keywords = ['rich', 'richtext', 'rich text', 'editor', 'wysiwg', 'builder', 'page builder', 'gutenberg', 'content'];
    protected $rules = [];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Editor'),
                'instructions' => __('statamic::fieldtypes.bard.config.section.editor.instructions'),
                'fields' => [
                    'buttons' => [
                        'display' => __('Buttons'),
                        'instructions' => __('statamic::fieldtypes.bard.config.buttons'),
                        'type' => 'bard_buttons_setting',
                        'full_width_setting' => true,
                        'default' => static::$defaultButtons,
                    ],
                    'smart_typography' => [
                        'display' => __('Smart Typography'),
                        'instructions' => __('statamic::fieldtypes.bard.config.smart_typography'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'save_html' => [
                        'display' => __('Save as HTML'),
                        'instructions' => __('statamic::fieldtypes.bard.config.save_html'),
                        'type' => 'toggle',
                    ],
                    'inline' => [
                        'display' => __('Inline'),
                        'instructions' => __('statamic::fieldtypes.bard.config.inline'),
                        'type' => 'select',
                        'cast_booleans' => true,
                        'options' => [
                            'false' => __('statamic::fieldtypes.bard.config.inline.disabled'),
                            'true' => __('statamic::fieldtypes.bard.config.inline.enabled'),
                            'break' => __('statamic::fieldtypes.bard.config.inline.break'),
                        ],
                        'default' => false,
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
                    ],
                    'reading_time' => [
                        'display' => __('Show Reading Time'),
                        'instructions' => __('statamic::fieldtypes.bard.config.reading_time'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'word_count' => [
                        'display' => __('Show Word Count'),
                        'instructions' => __('statamic::fieldtypes.bard.config.word_count'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'fullscreen' => [
                        'display' => __('Allow Fullscreen Mode'),
                        'instructions' => __('statamic::fieldtypes.bard.config.fullscreen'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'allow_source' => [
                        'display' => __('Allow Source Mode'),
                        'instructions' => __('statamic::fieldtypes.bard.config.allow_source'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'enable_input_rules' => [
                        'display' => __('Enable Input Rules'),
                        'instructions' => __('statamic::fieldtypes.bard.config.enable_input_rules'),
                        'type' => 'toggle',
                        'default' => true,
                        'validate' => 'accepted_if:smart_typography,true',
                    ],
                    'enable_paste_rules' => [
                        'display' => __('Enable Paste Rules'),
                        'instructions' => __('statamic::fieldtypes.bard.config.enable_paste_rules'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'remove_empty_nodes' => [
                        'display' => __('Remove Empty Nodes'),
                        'instructions' => __('statamic::fieldtypes.bard.config.remove_empty_nodes'),
                        'type' => 'select',
                        'cast_booleans' => true,
                        'options' => [
                            'false' => __("Don't remove empty nodes"),
                            'true' => __('Remove all empty nodes'),
                            'trim' => __('Remove empty nodes at the start and end'),
                        ],
                        'default' => 'false',
                    ],
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                    ],
                    'character_limit' => [
                        'display' => __('Character Limit'),
                        'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                        'type' => 'integer',
                    ],
                    'antlers' => [
                        'display' => 'Antlers',
                        'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                        'type' => 'toggle',
                    ],
                ],
            ],
            [
                'display' => __('Links'),
                'instructions' => __('statamic::fieldtypes.bard.config.section.links.instructions'),
                'fields' => [
                    'link_noopener' => [
                        'display' => __('Link Noopener'),
                        'instructions' => __('statamic::fieldtypes.bard.config.link_noopener'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'link_noreferrer' => [
                        'display' => __('Link Noreferrer'),
                        'instructions' => __('statamic::fieldtypes.bard.config.link_noreferrer'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'target_blank' => [
                        'display' => __('Target Blank'),
                        'type' => 'toggle',
                        'default' => false,
                        'instructions' => __('statamic::fieldtypes.bard.config.target_blank'),
                    ],
                    'link_collections' => [
                        'display' => __('Link Collections'),
                        'instructions' => __('statamic::fieldtypes.bard.config.link_collections'),
                        'type' => 'collections',
                        'mode' => 'select',
                    ],
                    'select_across_sites' => [
                        'display' => __('Select Across Sites'),
                        'instructions' => __('statamic::fieldtypes.bard.config.select_across_sites'),
                        'type' => 'toggle',
                    ],
                    'container' => [
                        'display' => __('Container'),
                        'instructions' => __('statamic::fieldtypes.bard.config.container'),
                        'type' => 'asset_container',
                        'mode' => 'select',
                        'max_items' => 1,
                        'if' => [
                            'buttons' => 'contains_any anchor, image',
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Sets'),
                'instructions' => __('statamic::fieldtypes.bard.config.section.sets.instructions'),
                'fields' => [
                    'sets' => [
                        'display' => __('Sets'),
                        'hide_display' => true,
                        'type' => 'sets',
                        'full_width_setting' => true,
                        'require_set' => false,
                    ],
                ],
            ],
            [
                'display' => __('Set Behavior'),
                'fields' => [
                    'always_show_set_button' => [
                        'display' => __('Always Show Set Button'),
                        'instructions' => __('statamic::fieldtypes.bard.config.always_show_set_button'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'collapse' => [
                        'display' => __('Collapse'),
                        'instructions' => __('statamic::fieldtypes.replicator.config.collapse'),
                        'type' => 'select',
                        'cast_booleans' => true,
                        'options' => [
                            'false' => __('statamic::fieldtypes.replicator.config.collapse.disabled'),
                            'true' => __('statamic::fieldtypes.replicator.config.collapse.enabled'),
                            'accordion' => __('statamic::fieldtypes.replicator.config.collapse.accordion'),
                        ],
                        'default' => false,
                    ],
                    'previews' => [
                        'display' => __('Field Previews'),
                        'instructions' => __('statamic::fieldtypes.bard.config.previews'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                ],
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
            if (
                is_string($value)
                || ($value instanceof Value && is_string($value->value())) // This part is not under test. See https://github.com/statamic/cms/pull/10104
            ) {
                return is_null($value) ? $value : $this->resolveStatamicUrls($value);
            }
        }

        if ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
        }

        $value = $this->convertLegacyTiptap($value);

        return (new Augmentor($this))->augment($value, $shallow);
    }

    public function process($value)
    {
        $value = $this->removeEmptyNodes($value);

        if ($this->config('inline')) {
            $value = $this->unwrapInlineValue($value);
        }

        $structure = collect($value)->map(function ($row, $index) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->processRow($row, $index);
        })->all();

        $structure = $this->runHooks('process', $structure);

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

    protected function removeEmptyNodes($value)
    {
        $value = collect($value);

        if ($this->config('remove_empty_nodes') === true) {
            $empty = $value->filter(function ($value) {
                return $this->shouldRemoveNode($value);
            });

            return $value->diffKeys($empty)->values();
        }

        if ($this->config('remove_empty_nodes') === 'trim') {
            if ($this->shouldRemoveNode($value->first())) {
                $value->shift();

                return $this->removeEmptyNodes($value);
            }

            if ($this->shouldRemoveNode($value->last())) {
                $value->pop();

                return $this->removeEmptyNodes($value);
            }
        }

        return $value;
    }

    protected function shouldRemoveNode($value)
    {
        $type = Arr::get($value, 'type');

        return in_array($type, ['heading', 'paragraph'])
            && ! Arr::has($value, 'content');
    }

    protected function shouldSaveHtml()
    {
        if ($this->config('sets')) {
            return false;
        }

        return $this->config('save_html');
    }

    protected function processRow($row, $index)
    {
        $row['attrs']['values'] = parent::processRow($row['attrs']['values'], $index);

        if (Arr::get($row, 'attrs.enabled', true) === true) {
            unset($row['attrs']['enabled']);
        }

        $row['attrs']['values'] = Arr::removeNullValues($row['attrs']['values']);

        return $row;
    }

    public function preProcess($value)
    {
        // Filter out broken nodes
        if (is_array($value)) {
            $value = collect($value)->filter(function ($node) {
                return array_key_exists('type', $node);
            })->values()->all();
        }

        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = str_replace('src="statamic://', 'src="', $value);
            $doc = (new Augmentor($this))->renderHtmlToProsemirror($value);
            $value = $doc['content'];
        } elseif ($this->isLegacyData($value)) {
            $value = $this->convertLegacyData($value);
        }

        $value = $this->convertLegacyTiptap($value);

        if ($this->config('inline')) {
            // Root should be text or br, if it's not this must be a block field converted
            // to inline. In that instance unwrap the content of the first node.
            if (! in_array($value[0]['type'], ['text', 'hardBreak'])) {
                $value = $this->unwrapInlineValue($value);
            }
            $value = $this->wrapInlineValue($value);
        } else {
            // Root should not be text or br, if it is this must be an inline field converted
            // to block. In that instance wrap the content in a paragraph node.
            if (in_array($value[0]['type'], ['text', 'hardBreak'])) {
                $value = $this->wrapInlineValue($value);
            }
        }

        $value = collect($value)->map(function ($row, $i) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->preProcessRow($row, $i);
        })->all();

        return $this->runHooks('pre-process', $value);
    }

    protected function preProcessRow($row, $index)
    {
        $values = parent::preProcessRow($row['attrs']['values'], $index);

        $generatedId = Arr::pull($values, '_id');

        return [
            'type' => 'set',
            'attrs' => [
                'id' => $row['attrs']['id'] ?? $generatedId,
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
        })->values()->all();

        $data = $this->runHooks('pre-process-index', $data);

        return (new Augmentor($this))->renderProsemirrorToHtml([
            'type' => 'doc',
            'content' => $data,
        ]);
    }

    public function extraRules(): array
    {
        $rules = [];

        $value = $this->field->value();

        if ($this->config('sets')) {
            $rules = collect($value)->filter(function ($set) {
                return $set['type'] === 'set';
            })->map(function ($set, $index) {
                $set = $set['attrs']['values'];

                return $this->setRules($set['type'], $set, $index);
            })->reduce(function ($carry, $rules) {
                return $carry->merge($rules);
            }, collect())->all();
        }

        return $this->runHooks('extra-rules', $rules);
    }

    protected function setRuleFieldPrefix($index)
    {
        return "{$this->field->handle()}.{$index}.attrs.values";
    }

    public function extraValidationAttributes(): array
    {
        $attributes = [];

        $value = $this->field->value();

        if ($this->config('sets')) {
            $attributes = collect($value)->filter(function ($set) {
                return $set['type'] === 'set';
            })->map(function ($set, $index) {
                $set = $set['attrs']['values'];

                return $this->setValidationAttributes($set['type'], $set, $index);
            })->reduce(function ($carry, $rules) {
                return $carry->merge($rules);
            }, collect())->all();
        }

        return $this->runHooks('extra-validation-attributes', $attributes);
    }

    public function isLegacyData($value)
    {
        if (is_string($value)) {
            return false;
        }

        if (! $setConfig = $this->flattenedSetsConfig()->all()) {
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
                $doc = (new Augmentor($this))->renderHtmlToProsemirror($set['text']);

                return $doc['content'];
            }

            return [
                [
                    'type' => 'set',
                    'attrs' => [
                        'id' => RowId::generate(),
                        'values' => $set,
                    ],
                ],
            ];
        })->all();
    }

    protected function convertLegacyTiptap($value)
    {
        if (is_string($value)) {
            return $value;
        }

        return collect($value)->map(function ($item, $key) {
            if (is_array($item) && $key === 'attrs') {
                return $item;
            }

            if (is_array($item)) {
                return $this->convertLegacyTiptap($item);
            }

            if ($key === 'type') {
                return Str::camel($item);
            }

            return $item;
        })->all();
    }

    public function preload()
    {
        $value = $this->field->value();

        $existing = collect($value)->filter(function ($item) {
            return $item['type'] === 'set';
        })->mapWithKeys(function ($set, $index) {
            $values = $set['attrs']['values'];

            return [$set['attrs']['id'] => $this->fields($values['type'], $index)->addValues($values)->meta()->put('_', '_')];
        })->toArray();

        $defaults = collect($this->flattenedSetsConfig())->map(function ($set, $handle) {
            return $this->fields($handle)->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all();
        })->all();

        $new = collect($this->flattenedSetsConfig())->map(function ($set, $handle) use ($defaults) {
            return $this->fields($handle)->addValues($defaults[$handle])->meta()->put('_', '_');
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

        $data = [
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
            'collapsed' => [],
            'previews' => $previews,
            '__collaboration' => ['existing'],
            'linkCollections' => $linkCollections,
            'linkData' => (object) $this->getLinkData($value),
        ];

        return $this->runHooks('preload', $data);
    }

    public function preProcessValidatable($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $value = $value ?? [];

        $value = collect($value)->map(function ($item, $index) {
            if ($item['type'] !== 'set') {
                return $item;
            }

            $values = $item['attrs']['values'];

            $processed = $this->fields($values['type'], $index)
                ->addValues($values)
                ->preProcessValidatables()
                ->values()
                ->all();

            $item['attrs']['values'] = array_merge($values, $processed);

            return $item;
        })->all();

        return $this->runHooks('pre-process-validatable', $value);
    }

    public function toGqlType()
    {
        return $this->config('sets') ? parent::toGqlType() : GraphQL::string();
    }

    public function addGqlTypes()
    {
        $types = collect($this->flattenedSetsConfig())
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

    private function wrapInlineValue($value)
    {
        return [[
            'type' => 'paragraph',
            'content' => $value,
        ]];
    }

    private function unwrapInlineValue($value)
    {
        return $value[0]['content'] ?? [];
    }

    public function runAugmentHooks($value)
    {
        return $this->runHooks('augment', $value);
    }

    public static function setDefaultButtons(array $buttons): void
    {
        static::$defaultButtons = $buttons;
    }
}
