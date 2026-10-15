<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Contracts\Routing\UrlBuilder;
use Statamic\Facades\Stache;

trait ResolvesEntryUri
{
    /**
     * The URI an entry will have once it's saved. A structured entry that
     * isn't in the tree yet goes under the given parent page.
     */
    protected function entryUri($entry, $tree, $parent)
    {
        if (! $entry->route()) {
            return null;
        }

        if (! $tree) {
            return app(UrlBuilder::class)
                ->content($entry)
                ->merge([
                    'id' => $entry->id() ?? Stache::generateId(),
                ])
                ->build($entry->route());
        }

        $parent = $parent ? $tree->find($parent) : null;

        if ($parent && $parent->isRoot()) {
            $parent = null;
        }

        return app(UrlBuilder::class)
            ->content($entry)
            ->merge([
                'parent_uri' => $parent ? $parent->uri() : null,
                'slug' => $entry->slug(),
                'depth' => $parent ? $parent->depth() + 1 : 1,
                'is_root' => false,
            ])
            ->build($entry->route());
    }
}
