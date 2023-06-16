<?php

namespace Statamic\Routing;

use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Site;
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

        if ($redirect === '@child') {
            $redirect = $this->firstChildUrl($parent);
        }

        if (Str::startsWith($redirect, 'entry::')) {
            $id = Str::after($redirect, 'entry::');
            $redirect = optional($this->findEntry($id, $parent, $localize))->url() ?? 404;
        }

        if (Str::startsWith($redirect, 'asset::')) {
            $id = Str::after($redirect, 'asset::');
            $redirect = optional(Facades\Asset::find($id))->url() ?? 404;
        }

        return is_numeric($redirect) ? (int) $redirect : $redirect;
    }

    private function findEntry($id, $parent, $localize)
    {
        if (! ($entry = Facades\Entry::find($id))) {
            return null;
        }

        if (! $localize) {
            return $entry;
        }

        $site = $parent instanceof Localization
            ? $parent->locale()
            : Site::current()->handle();

        return $entry->in($site) ?? $entry;
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
