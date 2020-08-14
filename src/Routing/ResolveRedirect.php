<?php

namespace Statamic\Routing;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Structures\Page;
use Statamic\Support\Str;

class ResolveRedirect
{
    public function __invoke($redirect, $parent = null)
    {
        if ($redirect === '@child') {
            $redirect = $this->firstChildUrl($parent);
        }

        if (Str::startsWith($redirect, 'entry::')) {
            $id = Str::after($redirect, 'entry::');
            $redirect = optional(Facades\Entry::find($id))->url() ?? 404;
        }

        return is_numeric($redirect) ? (int) $redirect : $redirect;
    }

    private function firstChildUrl($parent)
    {
        if (! $parent || ! $parent instanceof Entry) {
            throw new \Exception("Cannot resolve a page's child redirect without providing a page.");
        }

        if (! $parent instanceof Page && $parent instanceof Entry) {
            $parent = $parent->page();
        }

        $children = $parent->isRoot()
            ? $parent->structure()->in($parent->locale())->pages()->all()->slice(1, 1)
            : $parent->pages()->all();

        if ($children->isEmpty()) {
            return 404;
        }

        return $children->first()->url();
    }
}
