<?php

namespace Statamic\Forms;

class RenderableField
{
    public function __construct(protected $field, protected $data, protected $parser)
    {
        //
    }

    public function __toString()
    {
        $cascade = $this->parser->getCascade()->toArray();

        $data = array_merge($this->data, [
            'slot' => $cascade['form_fields_slot'],
        ]);

        return $this->minifyFieldHtml(
            view($this->field->fieldtype()->view(), $data)->render(),
        );
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
