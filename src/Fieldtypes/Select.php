<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Fields\Fieldtype;

use function Statamic\trans as __;

class Select extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $keywords = ['select', 'option', 'choice', 'dropdown', 'list'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    public function preload(): array
    {
        $options = $this->getOptions();

        if (! $this->config('taggable')) {
            return ['options' => $options];
        }

        $existing = collect($options)->pluck('value');

        $discovered = $this->discoverOptionsFromEntries()
            ->reject(fn ($value) => $existing->contains($value))
            ->map(fn ($value) => ['value' => $value, 'label' => $value])
            ->values()
            ->all();

        return ['options' => array_merge($options, $discovered)];
    }

    protected function discoverOptionsFromEntries(): IlluminateCollection
    {
        $field = $this->field();

        if (! $field || $field->parentField()) {
            return collect();
        }

        $collection = $this->collectionHandleFromParent();

        if (! $collection) {
            return collect();
        }

        $handle = $field->handle();

        return Blink::once("select-options-{$collection}-{$handle}", function () use ($collection, $handle) {
            return Entry::query()
                ->where('collection', $collection)
                ->pluck($handle)
                ->flatten()
                ->filter(fn ($value) => ! is_null($value) && $value !== '')
                ->unique()
                ->values();
        });
    }

    protected function collectionHandleFromParent(): ?string
    {
        $parent = $this->field()?->parent();

        if ($parent instanceof EntryContract) {
            return $parent->collectionHandle();
        }

        if ($parent instanceof CollectionContract) {
            return $parent->handle();
        }

        return null;
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.select.config.options'),
                        'type' => 'array',
                        'expand' => true,
                        'key_header' => __('Key'),
                        'value_header' => __('Label').' ('.__('Optional').')',
                        'add_button' => __('Add Option'),
                        'width' => '50',
                    ],
                    'taggable' => [
                        'display' => __('Allow additions'),
                        'instructions' => __('statamic::fieldtypes.select.config.taggable'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                        'type' => 'text',
                        'default' => '',
                        'width' => '50',
                    ],
                    'clearable' => [
                        'display' => __('Clearable'),
                        'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                    'searchable' => [
                        'display' => __('Searchable'),
                        'instructions' => __('statamic::fieldtypes.select.config.searchable'),
                        'type' => 'toggle',
                        'default' => true,
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'multiple' => [
                        'display' => __('Multiple'),
                        'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                        'width' => '50',
                        'if' => ['multiple' => true],
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'cast_booleans' => [
                        'display' => __('Cast Booleans'),
                        'instructions' => __('statamic::fieldtypes.any.config.cast_booleans'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'width' => '50',
                    ],
                ],
            ],
        ];
    }
}
