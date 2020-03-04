<?php

namespace Statamic\Routing;

use Statamic\Contracts\Entries\Entry;
use Statamic\Structures\Page;

class ResolveRedirect
{
    public function __invoke($redirect, $parent = null)
    {
        if ($redirect === '@child') {
            $redirect = $this->firstChildUrl($parent);
        }

        return is_numeric($redirect) ? (int) $redirect : $redirect;
    }

    private function firstChildUrl($parent)
    {
        if (!$parent || !$parent instanceof Entry) {
            throw new \Exception("Cannot resolve a page's child redirect without providing a page.");
        }

        if (!$parent instanceof Page && $parent instanceof Entry) {
            $parent = $parent->page();
        }

        $children = $parent->pages()->all();

        if ($children->isEmpty()) {
            return 404;
        }

        return $children->first()->url();
    }
}
