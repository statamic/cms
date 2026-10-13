<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Markdown;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\AutocompleteEditor\Mentions;
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
                'if' => ['markdown' => 'equals true'],
            ],
            'markdown' => [
                'display' => __('Markdown'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.markdown'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.inline'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
                'if' => ['markdown' => 'equals true'],
            ],
            'enable_line_breaks' => [
                'display' => __('Enable Line Breaks'),
                'instructions' => __('statamic::fieldtypes.autocomplete_editor.config.enable_line_breaks'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
                'if_any' => ['inline' => 'equals true', 'markdown' => 'equals false'],
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
            ->map(function ($item) {
                if (! Mentions::isValidValue($value = (string) $item['key'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid autocomplete_editor option value [%s]. Values may only contain letters, numbers, underscores, hyphens and periods.',
                        $value
                    ));
                }

                return ['value' => $value, 'label' => $item['value']];
            })
            ->values()
            ->all();
    }

    public function process($value)
    {
        return $value ?: null;
    }

    public function augment($value)
    {
        if (! $this->config('markdown', true)) {
            return $value === '' ? null : $value;
        }

        return static::parseMarkdown($value, (bool) $this->config('inline'));
    }

    // Static so a consumer holding a raw string, rather than a Value, can get the
    // same HTML without needing a field to read the config from.
    public static function parseMarkdown($value, bool $inline = false): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;
        $mentions = [];

        // An author's literal `\[\[ x \]\]` renders to `[[ x ]]`, which would be
        // indistinguishable from a real mention in the HTML. Real tokens are swapped
        // for alphanumeric sentinels (inert to the markdown parser) and the literals
        // that remain get their brackets encoded so Mentions::replace() skips them.
        // Backslashes before a real token (or a bracket touching one) are encoded
        // too, since replace() would otherwise read them as the plain text escape.
        do {
            $nonce = bin2hex(random_bytes(8));
        } while (str_contains($value, $nonce));

        $value = preg_replace_callback(Mentions::TOKEN, function ($matches) use (&$mentions, $nonce) {
            $sentinel = 'mention'.$nonce.'n'.count($mentions).'e';
            $mentions[$sentinel] = $matches[0];

            return $sentinel;
        }, $value);

        $html = Markdown::parser('default')->parse($value);

        $html = str_replace('[[', '&#91;&#91;', $html);
        $html = preg_replace_callback(
            '/\\\\+(?=\[*mention'.$nonce.'n\d+e)/',
            fn ($matches) => str_repeat('&#92;', strlen($matches[0])),
            $html
        );
        $html = strtr($html, $mentions);

        return $inline ? static::unwrapParagraph($html) : $html;
    }

    private static function unwrapParagraph(string $html): string
    {
        $html = trim($html);

        if (! preg_match('/^<p>(.*)<\/p>$/s', $html, $matches)) {
            return $html;
        }

        return str_contains($matches[1], '<p>') ? $html : $matches[1];
    }
}
