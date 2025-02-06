<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Support\Htmlable;

class RenderableField implements Htmlable
{
    protected $slot;
    protected $isBlade = false;

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

    public function isBlade(bool $isBlade): self
    {
        $this->isBlade = $isBlade;

        collect($this->data['fields'] ?? [])
            ->each(fn ($field) => $field['field']->isBlade($isBlade));

        return $this;
    }

    public function toHtml(): string
    {
        $data = array_merge($this->data, [
            'slot' => $this->slot,
        ]);

        return $this->minifyFieldHtml(
            view($this->field->fieldtype()->view(), $data)->render(),
        );
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    protected function minifyFieldHtml(string $html): string
    {
        // Leave whitespace around these html elements.
        $ignoredHtmlElements = collect(['a', 'span'])->implode('|');

        // Trim whitespace between all other html elements.
        $html = preg_replace('/\s*(<(?!\/*('.$ignoredHtmlElements.'))[^>]+>)\s*/', '$1', $html);

        return $html;
    }
}
