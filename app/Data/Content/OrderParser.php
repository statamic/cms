<?php

namespace Statamic\Data\Content;

use Statamic\Contracts\Data\Content\OrderParser as OrderParserContract;

class OrderParser implements OrderParserContract
{
    /**
     * Get the page order
     *
     * Possibilities:
     * path/to/2.page/index.md        => 2
     * path/to/2.page/locale.index.md => 2
     * path/to/page/index.md          => null
     * path/to/page/locale.index.md   => null
     * path/to/_2.page/index.md        => 2
     * path/to/_2.page/locale.index.md => 2
     * path/to/_page/index.md          => null
     * path/to/_page/locale.index.md   => null
     * path/to/__2.page/index.md        => 2
     * path/to/__2.page/locale.index.md => 2
     * path/to/__page/index.md          => null
     * path/to/__page/locale.index.md   => null
     *
     * @param string $path
     * @return mixed
     */
    public function getPageOrder($path)
    {
        $order = $this->match($path, '#\/(?:(?:_+)?(\d+)\.)?[\w_-]+\/(?:\w+\.)?index\.EXTENSION$#');

        return (is_numeric($order)) ? (int) $order : $order;
    }

    /**
     * Get the entry order
     *
     * Possibilities:
     * path/to/entry.md                 => null
     * path/to/1.entry.md               => 1
     * path/to/2015-01-15.entry.md      => "2015-01-15"
     * path/to/2015-01-15-1330.entry.md => "2015-01-15-1330"
     * path/to/_2015-01-15.entry.md       => "2015-01-15"
     * path/to/__2015-01-15-1330.entry.md => "2015-01-15-1330"
     *
     * Locales don't need special handling as they are just another directory.
     *
     * @param string $path
     * @return mixed
     */
    public function getEntryOrder($path)
    {
        $order = $this->match($path, '#\/(?:(?:_+)?([\d-]+)\.)?[\w_-]+\.EXTENSION$#');

        return (is_numeric($order)) ? (int) $order : $order;
    }

    /**
     * Get a match for for the status
     *
     * @param string $path    The path to match against
     * @param string $pattern The pattern to match.
     *                        The first capture group should be the status.
     *                        EXTENSION will be replaced by the file extension.
     * @return mixed
     */
    private function match($path, $pattern)
    {
        $ext = pathinfo($path)['extension'];

        $pattern = str_replace('EXTENSION', $ext, $pattern);

        preg_match($pattern, $path, $matches);

        return array_get($matches, 1);
    }
}
