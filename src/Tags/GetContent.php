<?php

namespace Statamic\Tags;

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\OutputsItems;

class GetContent extends Tags
{
    use OutputsItems;

    /**
     * {{ get_content:* }} ... {{ /get_content:* }}.
     */
    public function wildcard($tag)
    {
        return $this->entries(
           $this->context->value($tag)
        );
    }

    /**
     * {{ get_content from="" }} ... {{ /get_content }}.
     */
    public function index()
    {
        return $this->entries($this->params->get(['from', 'id']));
    }

    private function entries($items)
    {
        // $items may be a string containing a single ID or URI,
        // or it could be a string with multiple pipe-delimited IDs or URIs.
        if (! is_iterable($items)) {
            if (Str::contains($items, '|')) {
                $items = explode('|', $items);
            } else {
                $items = [$items];
            }
        }

        // We'll determine what's been provided by looking at the first item,
        // and assume that all the items provided are the same type.
        $first = $items[0];

        // If it's already an entry, we're done.
        // The user doesn't need to use this tag, but we'll let it work anyway.
        if ($first instanceof EntryContract) {
            return $this->output($items);
        }

        $usingUris = Str::startsWith($first, '/');

        $query = Entry::query()
            ->where('site', $this->params->get(['site', 'locale'], Site::current()->handle()))
            ->whereIn($usingUris ? 'uri' : 'id', $items);

        return $this->output($query->get());
    }
}
