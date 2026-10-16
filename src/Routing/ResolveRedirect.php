<?php

namespace Statamic\Routing;

use Statamic\Contracts\Entries\Entry;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Link;
use Statamic\Fieldtypes\Link\ArrayableLink;
use Statamic\Structures\Page;
use Statamic\Support\Str;

class ResolveRedirect
{
    public function __invoke($redirect, $parent = null, $localize = false)
    {
        return $this->resolve($redirect, $parent, $localize);
    }

    public function resolve($redirect, $parent = null, $localize = false)
    {
        if (is_null($redirect)) {
            return null;
        }

        if (! $item = $this->item($redirect, $parent, $localize)) {
            return 404;
        }

        return is_object($item) ? $item->url() : $item;
    }

    public function item($redirect, $parent = null, $localize = false)
    {
        if ($redirect instanceof ArrayableLink) {
            return $redirect->url();
        }

        if (is_null($redirect)) {
            return null;
        }

        if (is_array($redirect)) {
            $redirect = $redirect['url'];
        }

        if ($redirect === '@child') {
            return $this->firstChild($parent);
        }

        if ($redirect instanceof Values) {
            // Assume it's a `group` fieldtype with a `url` subfield.
            return $redirect->url->value();
        }

        foreach (Link::types() as $linkType) {
            if (Str::startsWith($redirect, "{$linkType->handle()}::")) {
                return $linkType->resolve(
                    id: Str::after($redirect, "{$linkType->handle()}::"),
                    parent: $parent,
                    localize: $localize
                );
            }
        }

        return is_numeric($redirect) ? (int) $redirect : $redirect;
    }

    private function firstChild($parent)
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
            return null;
        }

        return $children->first();
    }
}
