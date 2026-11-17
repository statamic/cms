<?php

namespace Statamic\Events;

/**
 * Dispatched right before the full measure static cache deletes a page from disk.
 *
 * The file still exists. Listeners that need to read it must run synchronously.
 *
 * Returning false does not prevent the deletion, but an exception thrown by a
 * synchronous listener does.
 */
class StaticCacheFileDeleting extends Event
{
    public function __construct(public string $path, public ?string $url, public ?string $site)
    {
    }
}
