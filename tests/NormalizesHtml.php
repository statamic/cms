<?php

namespace Tests;

trait NormalizesHtml
{
    /**
     * Normalize dynamically rendered HTML output so that it's easier to make assertions against it.
     *
     * @param  mixed  $html
     */
    protected function normalizeHtml($html)
    {
        // Remove new lines.
        $html = str_replace(["\n", "\r"], '', $html);

        // Trim whitespace between elements.
        $html = preg_replace('/>\s*([^<>]*)\s*</', '>$1<', $html);

        // Remove whitespace around radio and checkbox labels, etc.
        $html = preg_replace('/(>)\s*([^\s]+)\s*(<)/', '$1$2$3', $html);

        // Trim extraneous whitespace to a single space.
        $html = preg_replace('/\s+/m', ' ', $html);

        // Remove spaces at end of element where attributes are conditionally rendered.
        $html = str_replace(' >', '>', $html);

        return $html;
    }
}
