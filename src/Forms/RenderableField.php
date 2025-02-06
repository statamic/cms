<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Support\Htmlable;

class RenderableField implements Htmlable
{
    public $isBlade = false;

    public function __construct(protected $field, protected $data)
    {
        //
    }

    public function isBlade($isBlade)
    {
        $this->isBlade = $isBlade;

        collect($this->data['fields'] ?? [])
            ->each(fn ($field) => $field['field']->isBlade($isBlade));
    }

    public function toHtml()
    {
        $data = array_merge($this->data, [
            'slot' => new RenderableFieldSlot(app('form-slot'), $this->isBlade),
        ]);

        return $this->minifyFieldHtml(
            view($this->field->fieldtype()->view(), $data)->render(),
        );
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Minify field html.
     *
     * @param  string  $html
     * @return string
     */
    protected function minifyFieldHtml($html)
    {
        // Leave whitespace around these html elements.
        $ignoredHtmlElements = collect(['a', 'span'])->implode('|');

        // Trim whitespace between all other html elements.
        $html = preg_replace('/\s*(<(?!\/*('.$ignoredHtmlElements.'))[^>]+>)\s*/', '$1', $html);

        return $html;
    }
}
