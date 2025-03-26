<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Support\Htmlable;

class RenderableField implements Htmlable
{
    protected $slot;

    public function __construct(protected $field, protected $data)
    {
        //
    }

    public function slot(RenderableFieldSlot $slot): self
    {
        $this->slot = $slot;

        collect($this->data['fields'] ?? [])
            ->each(fn ($field) => $field['field']->slot($slot));

        return $this;
    }

    public function toHtml(): string
    {
        $data = array_merge($this->data, [
            'slot' => $this->slot,
        ]);

        return static::minify(
            view($this->field->fieldtype()->view(), $data)->render(),
        );
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * We minify renderable fieldtype html from our vendor publishable field partials,
     * because it makes makes things a bit more consistent and forgiving as far as
     * whitespace around textarea content, checkbox/radio labels, groups, etc.
     *
     * This allows us to format fieldtype partials nicely in a pleasing way that
     * makes sense to devs who are publishing and overriding fieldtype html.
     */
    public static function minify(string $html): string
    {
        // Leave whitespace around textually inline html elements.
        $ignoredHtmlElements = collect(['a', 'span'])->implode('|');

        // Trim whitespace between all other html elements.
        $html = preg_replace('/\s*(<(?!\/*('.$ignoredHtmlElements.'))[^>]+>)\s*/', '$1', $html);

        return $html;
    }
}
