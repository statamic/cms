<?php

namespace Statamic\Routing;

use Statamic\Structures\Page;

class ResolveRedirect
{
    public function __invoke($redirect, $parent = null)
    {
        if ($redirect === '@child') {
            return $this->firstChildUrl($parent);
        }

        return $redirect;
    }

    private function firstChildUrl($parent)
    {
        if (!$parent || !$parent instanceof Page) {
            throw new \Exception("Cannot resolve a page's child redirect without providing a page.");
        }

        $children = $parent->pages()->all();

        if (empty($children)) {
            return '404';
        }

        return $children->first()->url();
    }
}
