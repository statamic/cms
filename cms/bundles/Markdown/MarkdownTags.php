<?php

namespace Statamic\Addons\Markdown;

use Statamic\Extend\Tags;

class MarkdownTags extends Tags
{
    public function index()
    {
        return markdown($this->parse());
    }

    /**
     * Allow for indented markdown, without parsing everything as code.
     *
     * @return string
     */
    public function indent()
    {
        // Break up all the lines.
        $lines = collect(explode(PHP_EOL, $this->parse()));
        $regex = '/[^\s]/';

        // Find the first line with a non-whitespace character.
        $firstLine = $lines->search(function ($line) use ($regex) {
            return preg_match($regex, $line);
        });

        // Count the number of whitespace characters at the beginning of the
        // first line to prevent over-trimming.
        preg_match($regex, $lines[$firstLine], $matches, PREG_OFFSET_CAPTURE);
        $maxTrim = array_get($matches, '0.1');

        $md = $lines->map(function ($line) use ($maxTrim) {
            // Trim the appropriate amount of whitespace at the start of
            // each line.
            return preg_replace('/^\s{0,' . $maxTrim . '}/', '', $line);
        })->implode(PHP_EOL);

        return markdown($md);
    }
}
