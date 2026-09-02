<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Markdown;
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
            'antlers' => [
                'display' => __('Allow Antlers'),
                'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                'type' => 'toggle',
                'width' => 50,
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
        return $value ?: null;
    }

    public function augment($value)
    {
        return $this->parseMarkdown($value);
    }

    // Public so a consumer holding a raw config string, rather than a Value, can
    // get the same HTML without going through augmentation.
    public function parseMarkdown($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $html = Markdown::parser('default')->parse((string) $value);

        return $this->config('inline') ? $this->unwrapParagraph($html) : $html;
    }

    private function unwrapParagraph(string $html): string
    {
        $html = trim($html);

        if (! preg_match('/^<p>(.*)<\/p>$/s', $html, $matches)) {
            return $html;
        }

        return str_contains($matches[1], '<p>') ? $html : $matches[1];
    }
}
