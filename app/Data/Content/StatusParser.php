<?php

namespace Statamic\Data\Content;

use Statamic\Contracts\Data\Content\StatusParser as StatusParserContract;

class StatusParser implements StatusParserContract
{
    /**
     * Get the page publish status
     *
     * The order cascades down from the parent pages.
     *
     * Simple cases:
     * parent/child/index.md => true
     * parent/_child/index.md => false
     * parent/__child/index.md => false
     *
     * Simple inheritance:
     * parent/child/index.md => true
     * _parent/child/index.md => false
     * __parent/child/index.md => false
     *
     * Also accounts for a locale in all of these cases:
     * eg. parent/child/locale.index.md
     *
     * @param string $path
     * @return mixed
     */
    public function pagePublished($path)
    {
        // No underscores directly after a slash? It's live.
        return (! preg_match('/\/_/', $path));
    }

    /**
     * Get the entry publish status
     *
     * Possibilities:
     * path/to/entry.md                   => true
     * path/to/1.entry.md                 => true
     * path/to/2015-01-15.entry.md        => true
     * path/to/2015-01-15-1330.entry.md   => true
     * path/to/_entry.md                  => false
     * path/to/_1.entry.md                => false
     * path/to/_2015-01-15.entry.md       => false
     * path/to/_2015-01-15-1330.entry.md  => false
     * path/to/__entry.md                 => false
     * path/to/__1.entry.md               => false
     * path/to/__2015-01-15.entry.md      => false
     * path/to/__2015-01-15-1330.entry.md => false
     *
     * Locales don't need special handling as they are just another directory.
     *
     * @param string $path
     * @return mixed
     */
    public function entryPublished($path)
    {
        $ext = pathinfo($path)['extension'];

        $pattern = '#\/(_+)(?:(?:[\d-]+)?\.)?[\w_-]+\.'.$ext.'$#';

        preg_match($pattern, $path, $matches);

        return (! array_get($matches, 1));
    }
}
