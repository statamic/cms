<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class AutocompleteEditor extends Fieldtype
{
    protected $selectable = false;

    protected function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.options'),
                'type' => 'array',
                'expand' => true,
                'value_header' => __('Label').' ('.__('Optional').')',
                'field' => [
                    'type' => 'text',
                ],
            ],
            'trigger' => [
                'display' => __('Trigger'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.trigger'),
                'type' => 'text',
                'default' => '@',
                'width' => 50,
            ],
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.placeholder'),
                'type' => 'text',
                'width' => 50,
            ],
            'buttons' => [
                'display' => __('Buttons'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.buttons'),
                'type' => 'checkboxes',
                'options' => [
                    'bold' => __('Bold'),
                    'italic' => __('Italic'),
                    'h1' => __('Heading 1'),
                    'h2' => __('Heading 2'),
                    'h3' => __('Heading 3'),
                    'h4' => __('Heading 4'),
                    'h5' => __('Heading 5'),
                    'h6' => __('Heading 6'),
                    'bulletlist' => __('Unordered List'),
                    'orderedlist' => __('Ordered List'),
                ],
                'default' => ['bold', 'italic', 'h2', 'h3', 'bulletlist', 'orderedlist'],
            ],
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.inline'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'enable_line_breaks' => [
                'display' => __('Enable Line Breaks'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.enable_line_breaks'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
                'if' => ['inline' => 'equals true'],
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'options' => $this->getOptions(),
        ];
    }

    protected function getOptions(): array
    {
        $options = $this->config('options') ?? [];

        if (array_is_list($options) && ! is_array(Arr::first($options))) {
            $options = collect($options)->map(fn ($value) => ['key' => $value, 'value' => $value])->all();
        }

        if (Arr::isAssoc($options)) {
            $options = collect($options)->map(fn ($value, $key) => ['key' => $key, 'value' => $value])->all();
        }

        return collect($options)
            ->map(fn ($item) => ['value' => $item['key'], 'label' => $item['value']])
            ->values()
            ->all();
    }

    public function process($value)
    {
        if ($this->config('inline')) {
            $value = $this->unwrapInlineValue($value);
        }

        if (empty($value) || $value === [['type' => 'paragraph']]) {
            return null;
        }

        return $value;
    }

    public function preProcess($value)
    {
        $value = $this->removeBrokenNodes($value);

        if (empty($value)) {
            return [];
        }

        if ($this->config('inline')) {
            if (! in_array($value[0]['type'], ['text', 'hardBreak'])) {
                $value = $this->unwrapInlineValue($value);
            }
            $value = $this->wrapInlineValue($value);
        } elseif (in_array($value[0]['type'], ['text', 'hardBreak'])) {
            $value = $this->wrapInlineValue($value);
        }

        return $value;
    }

    protected function removeBrokenNodes($value)
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)->filter(function ($node) {
            return array_key_exists('type', $node);
        })->values()->all();
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
}
